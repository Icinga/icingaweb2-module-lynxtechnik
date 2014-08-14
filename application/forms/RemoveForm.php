<?php

namespace Icinga\Module\Lynxtechnik\Forms;

use Icinga\Data\Db\DbObject;
use Icinga\Module\Lynxtechnik\Web\Form\QuickForm;

class RemoveForm extends QuickForm
{
    protected $db;

    protected $object;

    public function setup()
    {
        $this->addElement('submit', $this->translate('Remove'), array('class' => 'link-like'));
    }

    public function onSuccess()
    {
        $this->object->delete();
    }

    public function setObject(DbObject $object)
    {
        $this->object = $object;
        return $this;
    }
}
