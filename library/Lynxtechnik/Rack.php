<?php

namespace Icinga\Module\Lynxtechnik;

use Icinga\Module\Lynxtechnik\Data\Db\DbObject;

class Rack extends DbObject
{
    protected $table = 'lynx_rack';

    protected $keyName = 'id';

    protected $autoincKeyName = 'id';

    protected $defaultProperties = array(
        'id'           => null,
        'room_id'      => null,
        'display_name' => null,
    );
}
