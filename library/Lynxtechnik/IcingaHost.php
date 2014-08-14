<?php

namespace Icinga\Module\Lynxtechnik;

use Icinga\Module\Lynxtechnik\Data\Db\DbObject;

class IcingaHost extends DbObject
{
    protected $table = 'lynx_icinga_host';

    protected $keyName = 'id';

    protected $autoincKeyName = 'id';

    protected $defaultProperties = array(
        'id'          => null,
        'template_id' => null,
        'host_name'   => null,
    );
}
