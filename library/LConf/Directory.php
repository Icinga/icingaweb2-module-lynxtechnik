<?php

/**
 * LConf_Directory class
 *
 * @package LConf
 */
/**
 * Allows to easily handle LConf directories.
 *
 * Usually you don't create LConf_Directory instances by yourself, you'll
 * rather ask your LConf_Connection to create them for you. Nonetheless, here
 * a sample code:
 *
 * <code>
 * $dir = LConf_Directory($lconf, 'LConf/NagiosConfig/Hosts');
 * </code>
 *
 * @author  Thomas Gelf <thomas@gelf.net>
 * @package LConf
 */
class LConf_Directory
{
    protected $local_dn;
    protected $array = array();
    protected $lconf;

    protected $key = 'ou';
    protected $top_key = 'ou';

    /**
     * Constructor
     */
    protected function __construct(LConf_Connection $lconf)
    {
        $this->lconf = $lconf;
    }

    /**
     * Creates a new LConf_Directory instance from a given array
     *
     * @param LConf_Connection  Your LConf_Connection object
     * @param Array             An array containing your directory parts
     * @return @LConf_Directory
     */
    public static function fromArray(LConf_Connection $lconf, $path = array(), $top_key = null)
    {
        $dir = new LConf_Directory($lconf);
        $dir->array = $path;
        if ($top_key !== null) $dir->top_key = $top_key;
        $dir->local_dn = $dir->arrayToLocalDN($path);

        return $dir;
    }

    public static function fromSeparatedString(LConf_Connection $lconf, $path, $separator)
    {
        $dir = new LConf_Directory($lconf);
        // TODO: handle quoted separators, e.g. 'Hosts/DNS \/ DHCP'
        $pattern = '/' . preg_quote($separator, '/') . '/';
        $parts = preg_split($pattern, $path, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($parts as $part) {
            if (empty($part)) {
                throw new Exception('Got invalid path: ' . $path);
            }
        }
        $dir->array = $parts;
        $dir->local_dn = $dir->arrayToLocalDN($parts);

        return $dir;
    }

    // TODO: Allow any object, not just directories
    public function linkTo(LConf_Directory $destination)
    {
        $alias = new LConf_Alias($this->getName(), $destination, array(
            'aliasedObjectName' => $this->getDN()
        ));
        $this->lconf->storeObject($alias);
    }

    public static function fromLocalDN(LConf_Connection $lconf, $dn)
    {
        $dir = new LConf_Directory($lconf);
        $dir->array = $dir->localDNToArray($dn);
        $dir->local_dn = $dn;

        return $dir;
    }

    public static function fromDN(LConf_Connection $lconf, $dn)
    {
        if (! $lconf->directory()->isLocalDN($dn)) {
            throw new LConf_Exception(sprintf(
                'DN "%s" is not to be found below this LConf connections root dn',
                $dn
            ));
        }
        return self::fromLocalDN($lconf, rtrim(substr($dn, 0, 0 - strlen($lconf->getRootDN())), ','));
    }

    public function create()
    {
        $this->lconf->createDirectory($this);
        return $this;
    }

    public function getName()
    {
        if (empty($this->array)) {
            throw new Exception('root directory has no OU');
        }
        return $this->array[count($this->array) - 1];
    }

    public function getDN(LConf_Connection $lconf = null)
    {
        if ($lconf === null) {
            $lconf = $this->lconf;
        }

        $ldn = $this->getLocalDN();
        if ($ldn !== '') {
            $ldn .= ',';
        }

        return $ldn . $lconf->getRootDN();
    }

    public function getLocalDN()
    {
        return $this->local_dn;
    }

    public function toArray()
    {
        return $this->array;
    }

    public function getConnection()
    {
        return $this->lconf;
    }

    protected function localDNToArray($dn)
    {
        $parts = LConf_Utils::explodeDN($dn);
        $array = array();
        foreach ($parts as $part) {
/*
// Temporarily disabled, host parent fake
            if (strtolower(substr($part, 0, 3)) !== $this->key . '=') {
                throw new Exception(sprintf(
                    'Given DN is not a valid LConf directory: %s',
                    $dn
                ));
            }
*/
            $array[] = substr($part, 3);
        }

        return array_reverse($array);
    }

    protected function arrayToLocalDN($array)
    {
        if (! is_array($array)) throw new Exception('Array required');
        $dn = '';
        foreach (array_reverse($array) as $pos => $part) {
            if ($dn !== '') { $dn .= ','; }
            $key = $pos === 0 ? $this->top_key : $this->key;
            $dn .= $key . '=' . LConf_Utils::quoteForDN($part);
        }
        return $dn;
    }

    public function toObject()
    {
        return $this->lconf->getStructuralObjectForDirectory($this);
    }

    protected function isLocalDN($dn)
    {
        $root = $this->lconf->getRootDN();

        return is_string($dn)
            && substr($dn, 0 - strlen($root)) === $root;
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function toString(LConf_Connection $lconf = null)
    {
        // Really? or name? or Path/To/Dir?
        return $this->getDN($lconf);
    }

    public function hasParent()
    {
        return (! empty($this->array));
    }

    public function getParent()
    {
        $dir = $this->array;
        array_pop($dir);

        return LConf_Directory::fromArray($this->lconf, $dir);
    }

    public function listSubDirectoryTree()
    {
        return $this->lconf->flattenTree(
            $this->lconf->getDirTree($this, $this->getParent())
        );
    }

    public function subDirectory($name, $top_key = null)
    {
        $dir = $this->array;
        $parts = preg_split('~/~', $name);
        foreach ($parts as $part) {
            $dir[] = $part;
        }

        if ($top_key === null) {
            $subdir = LConf_Directory::fromArray($this->lconf, $dir);
        } else {
            if ($this->top_key !== $this->key) {
                throw new Exception('No way...');
            }
            $subdir = LConf_Directory::fromArray($this->lconf, $dir, $top_key);
        }
        return $subdir;
    }
}

