<?php

abstract class LConf_Object
{
    protected $dir;
    protected $old_dir;

    protected $lconf;

    protected $name;
    protected $old_name;
    protected $key = 'cn';

    // TODO: remember changes
    protected $data = array();
    protected $old_data = array();

    protected $class_name;

    protected $attribute_map = array(
        'alias' => 'alias'
    );

    public function __construct($name, LConf_Directory $dir, $data = array())
    {
        $this->setName($name);
        $this->setDirectory($dir);
        $this->data = $data;
    }

    public function setAttributesFromNagiosDefinition(Nagios_Definition $def)
    {
        $map = array_flip($this->attribute_map);
        // TODO: ugly workaround, store flipped map instead:
        if (isset($this->attribute_map['hostcheckinterval'])) {
            $map['normal_check_interval'] = 'hostcheckinterval';
        }
        $attributes = array();
        foreach ($def->getAttributes() as $key => $val) {
            if ($key === 'use') continue;
            if (! isset($map[$key])) {
                echo "Ignoring $key = $val\n";
                continue;
            }
            $this->{ $map[$key] } = $val;
        }
        return $this;
    }

    public function setLoadedFromLConf(LConf_Connection $lconf)
    {
        $this->old_data = $this->data;
        $this->lconf = $lconf;
    }

    public function hasBeenLoadedFromLConf()
    {
        return ! empty($this->old_data);
    }

    public function store()
    {
        return $this->lconf->storeObject($this);
    }

    public function hasBeenRenamed()
    {
        return $this->old_name !== null;
    }

    public function hasBeenMoved()
    {
        return $this->old_dir !== null;
    }

    public function getOldDirectory()
    {
        // TODO: One far day -> handle changed connection
        return LConf_Directory::fromLocalDN(
            $this->lconf,
            $this->old_dir === null ? $this->dir : $this->old_dir
        );
    }

    public function getDirectory()
    {
        return LConf_Directory::fromLocalDN($this->lconf, $this->dir);
    }

    public function setDirectory(LConf_Directory $dir)
    {
        $new_dir = $dir->getLocalDN();
        if ($this->dir !== null && $this->old_dir === null && $this->hasBeenLoadedFromLConf()) {
            $this->old_dir = $this->dir;
        }
        $this->dir = $new_dir;
        $this->lconf = $dir->getConnection();
        if ($this->dir === $this->old_dir) {
            $this->old_dir = null;
        }
        return $this;
    }

    public function getObjectClassName()
    {
        return $this->class_name;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setStored(LConf_Connection $lconf)
    {
        $this->old_name = null;
        $this->old_dir = null;
        $this->old_data = $this->data;
        if ($lconf !== null) {
            $this->lconf = $lconf;
        }
        return $this;
    }

    public function getChanges()
    {
        $changes = (object) array(
            'added'    => array(),
            'deleted'  => array(),
            'modified' => array()
        );
        foreach ($this->data as $key => $val) {
            if ($this->old_data !== null
                && array_key_exists($key, $this->old_data))
            {
                if ($val !== $this->old_data[$key]) {
                    $changes->modified[$key] = $val;
                }
            } else {
                $changes->added[$key] = $val;
            }
        }
        if ($this->old_data !== null) {
            foreach ($this->old_data as $key => $val) {
                if (! isset($this->data[$key])) {
                    $changes->deleted[$key] = $val;
                }
            }
        }
        return $changes;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($value)
    {
        if ($this->name !== null && $this->old_name === null) {
            $this->old_name = $this->name;
        }
        $this->name = $value;
        if ($this->name === $this->old_name) {
            $this->old_name = null;
        }
        return $this;
    }

    public function setData($data)
    {
        foreach ($data as $k => $v) {
            $this->$k = $v;
        }
        return $this;
    }

    public function __set($key, $value)
    {
        if (strtolower($key) === 'dn') {
            throw new Exception('Direct DN modifications are not allowed');
        }
        if ($key == $this->key) {
            $this->setName($value);
        } else {
            $this->data[$key] = $value;
        }
    }

    public function __get($key)
    {
        if ($key == 'dn') return $this->getDN();
        if ($key == $this->key) return $this->name;
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }
        return null;
    }

    public function __isset($key)
    {
        if ($key == 'dn') return true;
        if ($key == $this->key) return true;
        if (array_key_exists($key, $this->data)) {
            if (is_array($this->data[$key]) && empty($this->data)) {
                return false;
            }
            return true;
        }
        return false;
    }

    public function __unset($key)
    {
        if (strtolower($key) === 'dn') {
            throw new Exception('Distinguished name (DN) cannot be removed');
        }
        if ($key == $this->key) {
            throw new Exception(sprintf(
                'Removing %s is not allowed',
                $this->key
            ));
        }
        if (array_key_exists($key, $this->data)) {
            $this->data[$key] = array();
        }
        return true;
    }

    public function getDN(LConf_Connection $lconf = null)
    {
        return sprintf(
            '%s,%s',
            $this->getRDN(),
            $this->getDirectory()->getDN($lconf)
        );
    }

    public function getRDN()
    {
        return sprintf(
            '%s=%s',
            $this->key,
            LConf_Utils::quoteForDN($this->getName())
        );
    }

    public function getOldName()
    {
        if ($this->old_name === null) {
            return $this->name;
        }
        return $this->old_name;
    }

    public function getOldDN(LConf_Connection $lconf)
    {
        return sprintf(
            '%s=%s,%s',
            $this->key,
            LConf_Utils::quoteForDN($this->getOldName()),
            $this->getOldDirectory()->getDN($lconf)
        );
    }

    public function __toString()
    {
        return $this->getName();
    }
}

