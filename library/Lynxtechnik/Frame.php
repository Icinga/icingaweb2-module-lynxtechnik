<?php

namespace Icinga\Module\Lynxtechnik;

use Icinga\Module\Lynxtechnik\Data\Db\DbObject;

class Rack extends DbObject
{
    protected $table = 'lynx_rack';

    protected $keyName = 'id';

    protected $autoincKeyName = 'id';

    protected $defaultProperties = array(
        'id'       => null,
        'rack_id'  => null,
        'position' => null,
        'height'   => null,
        'address'  => null,
    );
}
