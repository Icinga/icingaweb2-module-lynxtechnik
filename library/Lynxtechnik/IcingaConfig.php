<?php

namespace Icinga\Module\Lynxtechnik;

use Icinga\Module\Lynxtechnik\Db;

class IcingaConfig
{
    public static function fromDb(Db $db)
    {
        $services = $db->fetchServices();
        return $services;
    }
}
