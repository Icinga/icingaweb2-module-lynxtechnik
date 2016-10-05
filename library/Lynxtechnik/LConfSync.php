<?php

namespace Icinga\Module\Lynxtechnik;

use Icinga\Application\Icinga;
use Icinga\Data\ConfigObject;
use Icinga\Data\ResourceFactory;
use Icinga\Module\Lynxtechnik\Db;
use LConf_Connection;
use LConf_Loader;
use LConf_Service;
use Icinga\Exception\IcingaException;

class LConfSync
{
    protected $config;

    protected $db;

    protected $connection;

    public function __construct(ConfigObject $config, Db $db)
    {
        $this->config = $config;
        $this->db = $db;
    }

    public function isEnabled()
    {
        return $this->config->get('resource') !== null;
    }

    public function synchronize()
    {
        $lconf = $this->getConnection();
        $services = $this->db->fetchServices();
        foreach ($services as $service) {
            if (! $lconf->hasHostname($service->host_name)) {
                throw new IcingaException('LConf host %s does not exist', $service->host_name);
            }
        }

        $seenHosts = array();
        $seenServices = array();

        foreach ($services as $service) {
            $host = $lconf->fetchHostByName($service->host_name);
            $seenHosts[$host->dn] = $host;

            $base = $host->asParent();
            $cmd = 'check_lynx_service!' . $service->id;
            if ($lconf->hasService($service->service_description, $base)) {
                $service = $lconf->fetchServiceByName($service->service_description, $base);
                if ($service->serviceCheckCommand !== $cmd) {
                    $service->serviceCheckCommand = $cmd;
                    $service->store();
                }
            } else {
                $service = new LConf_Service($service->service_description, $base, array(
                    'serviceCheckCommand' => $cmd
                ));
                $service->store();
            }
            $seenServices[$service->dn] = $service;
        }

        foreach ($this->db->fetchHosts() as $host) {
            if (! $lconf->hasHostname($host->host_name)) {
                continue;
            }

            $host = $lconf->fetchHostByName($host->host_name);
            $base = $host->asParent();

            $existing = $lconf->fetchServicesByAttribute(
                'serviceCheckCommand',
                'check_lynx_service!*',
                $base
            );
            foreach ($existing as $service) {
                if (! array_key_exists($service->dn, $seenServices)) {
                    $lconf->removeObject($service);
                }
            }
        }
    }

    protected function getConnection()
    {
        if ($this->connection === null) {
            $this->prepareLConfAutoloader();
            $conf = ResourceFactory::getResourceConfig($this->config->get('resource'));
            $this->connection = new LConf_Connection($conf->toArray());
        }
        return $this->connection;
    }

    protected function prepareLConfAutoloader()
    {
        require_once Icinga::app()
            ->getModuleManager()
            ->getModule('lynxtechnik')
            ->getLibDir() . '/LConf/Loader.php';
        LConf_Loader::register();
    }
}
