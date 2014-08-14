<?php

namespace Icinga\Module\Lynxtechnik;

use Icinga\Module\Lynxtechnik\Data\Db\DbObject;

class Module extends DbObject
{
    protected $table = 'lynx_module';

    protected $keyName = 'id';

    protected $autoincKeyName = 'id';

}
