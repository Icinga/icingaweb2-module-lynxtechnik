<?php

/**
 * LConf_Connection class
 *
 * @package LConf
 */
/**
 * Backend class managing all the LDAP stuff for you.
 *
 * Usage example:
 *
 * <code>
 * $lconf = new LConf_Connection(array(
 *     'hostname' => 'localhost',
 *     'root_dn'  => 'dc=monitoring,dc=...',
 *     'bind_dn'  => 'cn=Mangager,dc=monitoring,dc=...',
 *     'bind_pw'  => '***'
 * ));
 * </code>
 *
 * @author  Thomas Gelf <thomas@gelf.net>
 * @package LConf
 */
class LConf_Connection
{
    protected $ds;
    protected $hostname;
    protected $bind_dn;
    protected $bind_pw;
    protected $root_dn;
    protected $prefix = 'lconf';

    /**
     * Constructor
     *
     * TODO: Allow to pass port and SSL options
     *
     * @param array LDAP connection credentials
     */
    public function __construct($config)
    {
        $this->hostname = $config['hostname'];
        $this->bind_dn  = $config['bind_dn'];
        $this->bind_pw  = $config['bind_pw'];
        $this->root_dn  = $config['root_dn'];
        if (isset($config['prefix'])) {
            $this->prefix = $config['prefix'];
        }
        $this->connect();
    }

    /**
     * Get this connections root (base) DN
     *
     * @return string
     */
    public function getRootDN()
    {
        return $this->root_dn;
    }

    /**
     * Shortcut to LConf_Directory
     *
     * Accepts either a string separated by slashed or a custom character
     * or an Array with all directory components. Will return an
     * LConf_Directory relating to this LConf_Connection.
     *
     * @param mixed  Path as string or array
     * @param string Optional custom separator
     * @return LConf_Directory
     */
    public function directory($path = null, $separator = '/')
    {
        if ($path === null) {
            $path = array();
        } elseif (is_string($path)) {
            return LConf_Directory::fromSeparatedString($this, $path, $separator);
        } elseif (! is_array($path)) {
            throw new Exception('Could not understand given directory');
        }
        return LConf_Directory::fromArray($this, $path);
    }

    /**
     * Whether the given directory already exists
     *
     * @param LConf_Directory Directory object
     * @return boolean
     */
    public function hasDirectory(LConf_Directory $dir)
    {
        $filter = '(objectClass=*)';
        $base = $dir->getDN($this);
        $fields = array('ou');
        // print "Searching for $filter in $base\n";
        $result = @ldap_search($this->ds, $base, $filter, $fields, 0, 1);
        if (! $result) {
            if ($base === $this->root_dn) {
                throw new Exception(sprintf(
                    'root DN %s does not exist',
                    $this->root_dn
                ));
            }
            return false;
        }
        return true;
    }

    /**
     * Creates the given LConf_Directory
     *
     * The given directory object will be created as an lconfstructuralObject
     * in your LDAP directory. Checks whether the objects already exists or not
     * before adding it to the LDAP tree. Recursively creates parent dirs in
     * case they're missing.
     *
     * @param LConf_Directory The to be created directory
     * @return void
     */
    public function createDirectory(LConf_Directory $dir)
    {
        $current = $dir;
        $missing = array();
        while (! $this->hasDirectory($current)) {
            $missing[] = $current;
            $current = $current->getParent();
        }
        foreach (array_reverse($missing) as $dir) {
            $this->mkdir($dir);
        }
    }

    /**
     * Effectively creates the given LConf_Directory
     *
     * Will blindly create the LDAP node for the given LConf_Directory
     *
     * @param LConf_Directory The to be created directory
     * @return void
     */
    protected function mkdir($dir)
    {
        $this->addNode($dir->getDN($this), array(
            'objectClass' => $this->prefix . 'StructuralObject',
            'ou'          => $dir->getName()
        ));
        return $this;
    }

    /**
     * Retrieve the whole "directory" structure as an array
     *
     * Still experimental, may be replaced with an object oriented approach
     *
     * @param string  Base DN, optional
     * @return array
     */
    public function getDirTree($base = null, $strip_base = false)
    {
        if ($base === null) $base = $this->root_dn;
        // Throws exception if not to be found below root DN:
        if ($base instanceof LConf_Directory) {
            $dir = $base;
            $base = $dir->getDN();
        } else {
            $dir = LConf_Directory::fromDN($this, $base);
        }

        $filter = sprintf('(&(objectClass=%sStructuralObject))', $this->prefix);
        $fields = array(); // any
        $result = @ldap_search($this->ds, $base, $filter, $fields);
        if (! $result) {
            throw new Exception('Invalid directory');
        }
        $list = array();

        $entries = $this->cleanupEntry(ldap_get_entries($this->ds, $result));
        $eentries = array();
        foreach ($entries as $key => $val) {
            if ($strip_base instanceof LConf_Directory) {
                $key = substr(
                    $key,
                    0,
                    strlen($key) - strlen($strip_base->getDN()) - 1
                );
            }
            $key = implode(',', array_reverse(preg_split('~,~', $key)));
            $eentries[$key] = $val;
        }
        ksort($eentries);

        $tree = array();
        foreach ($eentries as $key => $val) {
            $this->addToTree($key, $val, $tree);
        }

        return $tree;
    }

    public function flattenTree($tree, $cwd = array())
    {
        $flat = array();
        foreach ($tree as $k => $v) {
            $dir = $cwd;
            $dir[] = $k;
            if (isset($v['dn'])) {
                $impl = implode('/', $dir);
                $flat[] = $impl;
            }
            if (isset($v['children'])) {
                foreach ($this->flattenTree($v['children'], $dir) as $child) {
                    $flat[] = $child;
                }
            }
        }
        return $flat;
    }

    /**
     * Recursive helper function for getDirTree
     *
     * TODO: use LConf_Utils instead of PREG splitting
     *
     * @param string  Key string for the new value
     * @param array   Value, array of properties
     * @param tree    Tree array, passed by reference
     * @param string  ?? fullkey ??
     * @return void
     */
    protected function addToTree($key, $val, & $tree, $fullkey = null) {
        if ($fullkey === null) $fullkey = $key;
        if (preg_match('~,~', $key)) {
            list($lkey, $rest) = preg_split('~,~', $key, 2);
            $lkey = preg_replace('~^.+?=~', '', $lkey);
            if (! isset($tree[$lkey])) {
                $tree[$lkey] = array('children' => array());
            }
            $this->addToTree($rest, $val, $tree[$lkey]['children'], $fullkey);
        } else {
            $fullkey = implode(',', array_reverse(preg_split('~,~', $fullkey)));
            $tree[preg_replace('~^.+?=~', '', $key)] = $val + array('dn' => $fullkey);
        }
    }

    /**
     * Retrieve all host matching given attribute
     *
     * Returns an array containing all search results
     *
     * @param string  Attribute name
     * @param string  Search string
     * @return array
     */
    protected function searchHostsByAttribute($attribute, $search, $base = null)
    {
        return $this->searchObjectsByAttribute('Host', $attribute, $search, $base);
    }

    /**
     * Retrieve all services matching given attribute
     *
     * Returns an array containing all search results
     *
     * @param string  Attribute name
     * @param string  Search string
     * @return array
     */
    protected function searchServicesByAttribute($attribute, $search, $base = null)
    {
        return $this->searchObjectsByAttribute('Service', $attribute, $search, $base);
    }

    /**
     * Retrieve all objected of a specific type matching given attribute
     *
     * Returns an array containing all search results
     *
     * @param string  Object type
     * @param string  Attribute name
     * @param string  Search string
     * @return array
     */
    protected function searchObjectsByAttribute($type, $attribute, $search, $base = null)
    {
        return $this->wildcardSearchObjectsByAttribute(
            $type,
            $attribute,
            LConf_Utils::quoteForSearch($search),
            $base
        );
    }

    /**
     * Retrieve all objected of a specific type matching given attribute
     *
     * Returns an array containing all search results
     *
     * @param string  Object type
     * @param string  Attribute name
     * @param string  Search string
     * @return array
     */
    protected function wildcardSearchObjectsByAttribute($type, $attribute, $search, $base = null)
    {
        if ($base === null) {
            $base = $this->root_dn;
        } elseif ($base instanceof LConf_Directory) {
            $base = (string) $base;
        }
        $filter = sprintf(
            '(&(objectClass=%s%s)(%s=%s))',
            $this->prefix,
            $type,
            $attribute,
            $search
        );

        $fields = array(); // any
        $result = @ldap_search($this->ds, $base, $filter, $fields);
        if (! $result) {
            die('Got no result: ' . $base . ' => ' . $filter);
        }
        $list = array();
        $entries = ldap_get_entries($this->ds, $result);

        return $this->cleanupEntry($entries);
    }

    public function getStructuralObjectForDirectory(LConf_Directory $dir)
    {
        $dn = (string) $dir;
        $filter = sprintf('(objectClass=%sStructuralObject)', $this->prefix);
        $fields = array(); // any
        $result = @ldap_search($this->ds, (string) $dir, $filter, $fields, 0, 1);
        if (! $result) {
            $obj = new LConf_StructuralObject($dir->getName(), $dir->getParent());
return $obj;
        }

        $entries = ldap_get_entries($this->ds, $result);
        $entries = $this->cleanupEntry($entries);

        $data = array();
        if (! isset($entries[$dn])) {
            $obj = new LConf_StructuralObject($dir->getName(), $dir->getParent());
        } else {

          foreach ($entries[$dn] as $key => $value) {
            if (in_array($key , array('description', 'objectclass', 'cn', 'ou'))) {
                continue;
            }
            if (substr($key, 0, strlen($this->prefix)) !== $this->prefix) {
                throw new Exception(sprintf(
                    'Got invalid object key %s',
                    $key
                ));
            }
            $key = substr($key, strlen($this->prefix));
            $data[$key] = $value;
          }
          $obj = new LConf_StructuralObject($dir->getName(), $dir->getParent(), $data);
          $obj->setLoadedFromLConf($this);
        }
        return $obj;
    }

    /**
     * Whether there exists a host object with the given IP address
     *
     * Looks for address fields matching this IP address. This function does
     * pure string comparsion and has absolutely no knowledge of IP address
     * notations - neither IPv4 nor IPv6. The functions only intent is to
     * give people using real IP addresses in their Nagios/Icinga config the
     * chance to write more fluent code.
     *
     * @param  string  IP address to search for
     * @return boolean
     */
    public function hasIp($ip)
    {
        return count($this->searchHostsByAddress($ip)) > 0;
    }

    /**
     * Checks whether the given hostname already exists
     *
     * This function tries to find host entries with a CN matching the given
     * hostname
     *
     * @param  string   Hostname to search for
     * @return boolean
     */
    public function hasHostname($hostname, $base = null)
    {
        return count($this->searchHostsByName($hostname, $base)) > 0;
    }

    public function hasService($cmd, $base = null)
    {
        return count($this->searchObjectsByAttribute('Service', 'cn', $cmd, $base)) > 0;
    }

    public function hasCommand($cmd, $base = null)
    {
        return count($this->searchObjectsByAttribute('Command', 'cn', $cmd, $base)) > 0;
    }


    public function hasContact($name)
    {
        return count($this->searchObjectsByAttribute('Contact', 'cn', $name, $base)) > 0;
    }

    /**
     * Retrieve a list of all hosts matching the given CN
     *
     * @param  string   Hostname/CN to search for
     * @return array
     */
    protected function searchHostsByName($hostname, $base = null)
    {
        return $this->searchHostsByAttribute('cn', $hostname, $base);
    }

    /**
     * Retrieve a list of all hosts with the given Address
     *
     * @param  string   Address string to search for
     * @return array
     */
    protected function searchHostsByAddress($address)
    {
        return $this->searchHostsByAttribute($this->prefix . 'Address', $address);
    }

    /**
     * Retrieve a list of all services matching the given CN
     *
     * @param  string   Service/CN to search for
     * @return array
     */
    protected function searchServicesByName($service, $base = null)
    {
        return $this->searchServicesByAttribute('cn', $service, $base);
    }

    /**
     * Fetch all LConf_Host from LDAP tree matching the given hostname
     *
     * @param  string     Hostname to search for
     * @param  string     Hostname to search for
     * @return LConf_Host
     */
    public function fetchServicesByAttribute($attribute, $search, $base = null)
    {
        if (! in_array($attribute , array('description', 'objectclass', 'cn'))) {
            $attribute = ucfirst($this->prefix . ucfirst($attribute));
        }
        $result = $this->wildcardSearchObjectsByAttribute('Service', $attribute, $search, $base);
        $services = array();

        foreach ($result as $dn => $entry) {
            $data = array();
            foreach ($entry as $key => $value) {
                if (in_array($key , array('description', 'objectclass', 'cn'))) {
                    continue;
                }
                if (substr($key, 0, strlen($this->prefix)) !== $this->prefix) {
                    throw new Exception(sprintf(
                        'Got invalid object key %s',
                        $key
                    ));
                }
                $key = substr($key, strlen($this->prefix));
                $data[$key] = $value;
            }
            $parts = LConf_Utils::explodeDN($dn);
            $first = preg_replace('~^cn=~', '', array_shift($parts));
            $pdn = LConf_Utils::implodeDN($parts);
            $dir = LConf_Directory::fromDN($this, $pdn);
            $parts = $dir->toArray();
            $service = new LConf_Service($first, $dir, $data);
            $service->setLoadedFromLConf($this);
            $services[$dn] = $service;
        }

        return $services;
    }

    /**
     * Returns an LConf_Host object created from a given search result
     *
     * Search result must contain exactly one host
     *
     * @param  array      Search result
     * @param  string      Search result
     * @return LConf_Host
     */
    protected function getSingleLConfHost($hosts, $key)
    {
        if (! $hosts) {
            throw new Exception(sprintf('Unable to fetch host %s', $key));
        }
        if (count($hosts) !== 1) {
            throw new Exception(sprintf('Host %s is not unique', $key));
        }
        $dn = key($hosts);
        $data = array();

        // Loop through the first (and only) hosts attributes, remove LConf
        // prefix from all but the well-known prefix-less attributes
        // TODO: 'description' will get lost this way, is this ok?
        //       Probably not.
        foreach ($hosts[$dn] as $key => $value) {
            if (in_array($key , array('description', 'objectclass', 'cn'))) {
                continue;
            }
            if (substr($key, 0, strlen($this->prefix)) !== $this->prefix) {
                throw new Exception(sprintf(
                    'Got invalid object key %s',
                    $key
                ));
            }
            $key = substr($key, strlen($this->prefix));
            $data[$key] = $value;
        }
        $parts = LConf_Utils::explodeDN($dn);
        $first = preg_replace('~^cn=~', '', array_shift($parts));
        $pdn = LConf_Utils::implodeDN($parts);
        $dir = LConf_Directory::fromDN($this, $pdn);
        $parts = $dir->toArray();
        $host = new LConf_Host($first, $dir, $data);
        $host->setLoadedFromLConf($this);
        return $host;
    }

    /**
     * Returns an LConf_Service object created from a given search result
     *
     * Search result must contain exactly one service
     *
     * @param  array      Search result
     * @param  string      Search result
     * @return LConf_Service
     */
    protected function getSingleLConfService($services, $key)
    {
        if (! $services) {
            throw new Exception(sprintf('Unable to fetch service %s', $key));
        }
        if (count($services) !== 1) {
            throw new Exception(sprintf('Service %s is not unique', $key));
        }
        $dn = key($services);
        $data = array();

        // Loop through the first (and only) service attributes, remove LConf
        // prefix from all but the well-known prefix-less attributes
        // TODO: 'description' will get lost this way, is this ok?
        //       Probably not.
        foreach ($services[$dn] as $key => $value) {
            if (in_array($key , array('description', 'objectclass', 'cn'))) {
                continue;
            }
            if (substr($key, 0, strlen($this->prefix)) !== $this->prefix) {
                throw new Exception(sprintf(
                    'Got invalid object key %s',
                    $key
                ));
            }
            $key = substr($key, strlen($this->prefix));
            $data[$key] = $value;
        }
        $parts = LConf_Utils::explodeDN($dn);
        $first = preg_replace('~^cn=~', '', array_shift($parts));
        $pdn = LConf_Utils::implodeDN($parts);
        $dir = LConf_Directory::fromDN($this, $pdn);
        $parts = $dir->toArray();
        $service = new LConf_Service($first, $dir, $data);
        $service->setLoadedFromLConf($this);
        return $service;
    }

    protected function getSingleLConfCommand($commands, $key)
    {
        $dn = key($commands);
        $data = array(
            'commandline' => $commands[$dn][$this->prefix . 'commandline']
        );
        $parts = LConf_Utils::explodeDN($dn);
        array_shift($parts);
        $pdn = implode(',', $parts);
        return new LConf_Command($dn, LConf_Directory::fromDN($this, $pdn), $data);
    }

    protected function getSingleLConfContact($contacts, $key)
    {
        $dn = key($contacts);

        $data = array();
        foreach ($contacts[$dn] as $key => $value) {
            if (in_array($key , array('description', 'objectclass', 'cn'))) {
                continue;
            }
            if (substr($key, 0, strlen($this->prefix)) !== $this->prefix) {
                throw new Exception(sprintf(
                    'Got invalid object key %s',
                    $key
                ));
            }
            $key = substr($key, strlen($this->prefix));
            $data[$key] = $value;
        }
        $parts = LConf_Utils::explodeDN($dn);
        array_shift($parts);
        $pdn = implode(',', $parts);
        return new LConf_Contact($dn, LConf_Directory::fromDN($this, $pdn), $data);
    }

    /**
     * Fetch a single LConf_Host from LDAP tree matching the given hostname
     *
     * @param  string     Hostname to search for
     * @return LConf_Host
     */
    public function fetchHostByName($hostname, $base = null)
    {
        $hosts = $this->searchHostsByName($hostname, $base);
        return $this->getSingleLConfHost($hosts, $hostname);
    }

    /**
     * Fetch a single LConf_Service from LDAP tree matching the given service desc
     *
     * @param  string     service_description to search for
     * @return LConf_Service
     */
    public function fetchServiceByName($service, $base = null)
    {
        $services = $this->searchServicesByName($service, $base);
        return $this->getSingleLConfService($services, $service);
    }

    /**
     * Remove a single LConf_Host from LDAP tree matching the given hostname
     *
     * @param  string     Hostname to search for
     * @return LConf_Host
     */
    public function removeHostByName($hostname)
    {
        $hosts = $this->searchHostsByName($hostname);
        $host = $this->getSingleLConfHost($hosts, $hostname);
        return ($this->removeNode($host->getDN()));
    }

    /**
     * Fetch all LConf_Host from LDAP tree matching the given hostname
     *
     * @param  string     Hostname to search for
     * @return LConf_Host
     */
    public function fetchHostsByName($hostname)
    {
        $hosts = $this->wildcardSearchObjectsByAttribute('host', 'cn', $hostname);
        $hostarr = Array();
        foreach ($hosts as $key => $value) {
            $hostarr[] = $value['cn'];
        }

        return $hostarr;
    }

    /**
     * Fetch a single LConf_Host from LDAP tree matching the given IP address
     *
     * For farther details please see the description of the hasIp() method
     *
     * @param  string     IP address to search for
     * @return LConf_Host
     */
    public function fetchHostByIp($ip)
    {
        return $this->fetchHostByAddress($ip);
    }

    /**
     * Fetch a single LConf_Host from LDAP tree matching the given host address
     *
     * @param  string     Address to search for
     * @return LConf_Host
     */
    public function fetchHostByAddress($address)
    {
        $hosts = $this->searchHostsByAddress($address);
        return $this->getSingleLConfHost($hosts, $address);
    }

    public function fetchCommandByName($cmd)
    {
        $commands = $this->searchObjectsByAttribute('Command', 'cn', $cmd);
        return $this->getSingleLConfCommand($commands, $cmd);
    }

    public function fetchContactByName($name)
    {
        $contacts = $this->searchObjectsByAttribute('Contact', 'cn', $name);
        return $this->getSingleLConfContact($contacts, $name);
    }

    /**
     * Store the given LConf_Object to your LDAP tree
     *
     * @param  LConf_Object Object to be stored
     * @param  boolean      Whether the parent dir shall be created if missing
     * @return void
     */
    public function storeObject(LConf_Object $object, $create_dir_if_missing = true)
    {
        $data = $object->getData();
        $class = $object->getObjectClassName();
        if (is_array($class)) {
           $fields = array(
                'objectclass' => $object->getObjectClassName(),
                // 'cn' => $object->cn
            );
        } else {
            $fields = array(
                'objectclass' => $this->prefix . $object->getObjectClassName(),
                // 'cn' => $object->cn
            );
        }
        foreach ($data as $key => $value) {
            if (strtolower($key) === 'aliasedobjectname') {
                $fields[$key] = $value;
            } else {
                $fields[$this->prefix . $key] = $value;
            }
        }
        if ($create_dir_if_missing) {
            $this->createDirectory($object->getDirectory());
        }
        if ($object->hasBeenRenamed() || $object->hasBeenMoved()) {
            $errstr = null;
            if (! $this->renameNode($object->getOldDN($this), $object->getRDN(), $object->getDirectory()->getDN(), $errstr)) {
                throw new Exception(sprintf(
                    "Could not rename %s to %s,%s: %s",
                    $object->getOldDN($this),
                    $object->cn,
                    $object->getDirectory()->getDN(),
                    $errstr
                ));
            }
        }
        $this->setNode($object->getDN($this), $fields);

        // Experimental:
        if ($object instanceof LConf_Host && $object->hasServiceDetails()) {
            foreach ($object->getServiceDetails() as $sname => $sdetails) {
                $fields = array(
                    'objectclass' => $this->prefix . 'Service',
                );
                foreach ($sdetails as $key => $value) {
                    $fields[$this->prefix . $key] = $value;
                }
                $dn = 'cn=' . LConf_Utils::quoteForDN($sname) . ',' . $object->getDN($this);
                $this->setNode($dn, $fields);
            }
        }
        // END Experimental
    }

    /**
     * Clean up a ldapsearch() result entry, removing useless information
     *
     * @param  array Search result entry
     * @return array
     */
    protected function cleanupEntry($entry)
    {
      $retEntry = array();
      for ( $i = 0; $i < $entry['count']; $i++ ) {
          if (is_array($entry[$i])) {
              $subtree = $entry[$i];
              $retEntry[$subtree['dn']] = $this->cleanupEntry($subtree);
          } else {
              $attribute = $entry[$i];
              if ( $entry[$attribute]['count'] == 1 ) {
                  $retEntry[$attribute] = $entry[$attribute][0];
              } else {
                  for ( $j = 0; $j < $entry[$attribute]['count']; $j++ ) {
                      $retEntry[$attribute][] = $entry[$attribute][$j];
                  }
              }
          }
      }
      return $retEntry;
    }

    protected function listEntries($search_dn)
    {
        // objectClass = $prefix. 'StructuralObject', 'Host', 'Service'...
        $filter = "objectClass=*";
        $fields = array(
            'dn',
            'objectclass',
            'aliasedobjectname'
        );
        $fields = array(); // any?
        $result = @ldap_list($this->ds, $search_dn, $filter, $fields);
        if (! $result) {
            die('Got no result: ' . $search_dn . ' => ' . $filter);
        }
        $list = array();

        $entries = ldap_get_entries($this->ds, $result);
        return $this->cleanupEntry($entries);
    }

    protected function renameNode($old_dn, $rdn, $newdir, & $errstr = '')
    {
        $res = @ldap_rename($this->ds, $old_dn, $rdn, $newdir, true);
        if ($res === false) {
            $errstr = ldap_error($this->ds);
        }
        return $res;
    }

    public function tmpGetNode($dn, $fields = array())
    {
        return $this->getNode($dn, $fields);
    }

    protected function getNode($dn, $fields = array())
    {
        $result = @ldap_read($this->ds, $dn, 'objectclass=*', $fields);
        if ($result === false) {
            return false;
        }
        $entries = ldap_get_entries($this->ds, $result);
        return $this->cleanupEntry($entries[0]);
    }

    protected function setNode($dn, $fields = array())
    {
        $node = $this->getNode($dn);
        if ($node === false) {
            $this->addNode($dn, $fields);
        } else {
            $this->modifyNode($dn, $fields);
        }
    }

    protected function addNode($dn, $fields)
    {
        $fields = array_filter($fields);
        $result = @ldap_add($this->ds, $dn, $fields);
        if ($result === false) {
            throw new Exception(sprintf(
                'Unable to add %s: %s',
                $dn,
                ldap_error($this->ds)
            ) . json_encode($fields));
        }
    }

    protected function removeNode($dn)
    {
        $result = @ldap_delete($this->ds, $dn);
        if ($result === false) {
            throw new Exception(sprintf(
                'Unable to delete %s: %s',
                $dn,
                ldap_error($this->ds)
            ));
        }
    }

    public function removeObject(LConf_Object $obj)
    {
        $result = @ldap_delete($this->ds, $obj->dn);
        if ($result === false) {
            throw new Exception(sprintf(
                'Unable to delete %s: %s',
                $obj->dn,
                ldap_error($this->ds)
            ));
        }
    }

    protected function modifyNode($dn, $fields)
    {
        $result = @ldap_modify($this->ds, $dn, $fields);
        if ($result === false) {
            throw new Exception(sprintf(
                'Unable to modify %s: %s',
                $dn,
                ldap_error($this->ds)
            ));
        }
    }

    protected function connect()
    {
        $this->ds = ldap_connect($this->hostname);
        // ldap_rename requires LDAPv3:
        if (!ldap_set_option($this->ds, LDAP_OPT_PROTOCOL_VERSION, 3)) {
            throw new Exception('LDAPv3 is required');
        }
        // ldap_set_option($this->ds, LDAP_OPT_REFERRALS, 0);
        // ldap_set_option($this->ds, LDAP_OPT_DEREF, LDAP_DEREF_NEVER);

        $r = @ldap_bind($this->ds, $this->bind_dn, $this->bind_pw);
        if (! $r) {
            throw new Exception(sprintf(
                'LDAP connection (%s / %s) failed: %s',
                $this->bind_dn,
                $this->bind_pw,
                ldap_error($this->ds)
            ));
        }

    }
}

