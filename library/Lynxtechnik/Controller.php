<?php

namespace Icinga\Module\Lynxtechnik;

use Icinga\Module\Lynxtechnik\Data\Db\DbObject;

class Controller extends DbObject
{
    protected $table = 'lynx_controller';

    protected $keyName = 'id';

    protected $autoincKeyName = 'id';

    protected $defaultProperties = array(
        'id'             => null,
        'frame_id'       => null,
        'ip_address'     => null,
        'community'      => 'public',
        'last_discovery' => null,
    );

    public function mungeIp_address($value)
    {
        return ip2long($value);
    }
}
