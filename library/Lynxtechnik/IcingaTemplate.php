<?php

namespace Icinga\Module\Lynxtechnik;

use Icinga\Module\Lynxtechnik\Data\Db\DbObject;

class IcingaTemplate extends DbObject
{
    protected $table = 'lynx_icinga_template';

    protected $keyName = 'id';

    protected $autoincKeyName = 'id';

    protected $defaultProperties = array(
        'id'    => null,
        'name'  => null,
        'title' => null,
        'type'  => 'host'
    );
}
