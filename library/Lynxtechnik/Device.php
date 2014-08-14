<?php

namespace Icinga\Module\Lynxtechnik;

use Exception;

// Replaced by Perl variant

class Device
{
    protected $ip;
    protected $community;
    protected $info;
    protected $devices;
    protected $snmp;

    public function __construct($ip, $community = 'public')
    {
        $this->ip = $ip;
        $this->community = $community;
        snmp_set_oid_output_format(SNMP_OID_OUTPUT_NUMERIC);
        snmp_set_valueretrieval(SNMP_VALUE_OBJECT);
    }

    protected function fetchData()
    {
        $mib2 = '.1.3.6.1.2.1.1';
        $oids = array(
            'sysDescr'    => $mib2 . '.1',
            'sysObjectId' => $mib2 . '.2',
            'sysUptime'   => $mib2 . '.3',
            'sysContact'  => $mib2 . '.4',
            'sysName'     => $mib2 . '.5',
            'sysLocation' => $mib2 . '.6',
            'sysServices' => $mib2 . '.7',
        );
        $this->info = $this->fetchOids($oids);
//        $this->snmp = new SNMP(SNMP::VERSION_2C, $this->ip, $this->community);
    }

    protected function isLynxDevice()
    {
        return $this->info->sysObjectId === '.1.3.6.1.4.1.14755';
    }

    protected function assertLynxDevice()
    {
        if (! $this->isLynxDevice()) {
            throw new Exception(sprintf('%s is not a Lynx device', $this->ip));
        }
    }

    protected function fetchLynx()
    {
        $this->assertLynxDevice();
        $this->devices = $this->getNextOid($this->info->sysObjectId);
    }

    protected function getNextOid($oid)
    {
 //       $res = $this->snmp->getnext(array($oid));
        return $res;
    }

    protected function fetchOids($oids)
    {
        $result = array();
        foreach($oids as $name => $oid) {
            $res = snmp2_get($this->ip, $this->community, $oid);
            // http://de2.php.net/manual/de/snmp.constants.php
            switch($res->type) {
                case SNMP_TIMETICKS:         // 67
                case SNMP_INTEGER:           // 2
                    $value = (int) $res->value;
                    //break;
                case SNMP_OCTET_STR:         // 4
                    $value = $res->value;
                    break;
                case SNMP_OBJECT_ID:         // 6
                    $value = $res->value;
                    break;
                default:
                    $value = '[' . $res->type . '] ' . $res->value;
            }
            $result[$name] = $value;
        }
        return (object) $result;
    }

    public function dump()
    {
        $this->fetchData();
        $this->fetchLynx();
        return (object) array(
            'ip'   => $this->ip,
            'info' => $this->info,
            'devices' => $this->devices,
        );
    }
}
