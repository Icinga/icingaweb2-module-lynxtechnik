<?php

class LConf_Loader
{
    private static $instance;

    protected $basedir;

    private final function __construct()
    {
        $this->basedir = dirname(__DIR__);
    }

    public static function register()
    {
        self::getInstance();
    }

    public function loadClass($class)
    {
        if (substr($class, 0, 6) !== 'LConf_') return false;

        $file = preg_replace('~_~', DIRECTORY_SEPARATOR, $class) . '.php';
        require_once $this->basedir . DIRECTORY_SEPARATOR . $file;
    }

    protected function reallyRegister()
    {
        spl_autoload_register(array($this, 'loadClass'));
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new static();
            self::$instance->reallyRegister();
        }
        return self::$instance;
    }
}

