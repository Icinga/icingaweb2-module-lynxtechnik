<?php

namespace Icinga\Module\Lynxtechnik\Forms;

use Icinga\Module\Lynxtechnik\Controller;
use Icinga\Module\Lynxtechnik\Web\Form\QuickForm;

class ControllerForm extends QuickForm
{
    protected $db;

    protected $object;

    public function setup()
    {
        $this->addElement('text', 'ip_address', array(
            'label' => $this->translate('IP address'),
            'required' => true,
            'description' => $this->translate('LYNX Technik controller ip address')
        ));
        $this->addElement('text', 'community', array(
            'label' => $this->translate('Community'),
            'required' => true,
            'description' => $this->translate('SNMP Community string (v2 only)')
        ));
        $this->addElement('submit', $this->translate('Store'));
    }

    public function onSuccess()
    {
        if ($this->object) {
            $this->object->setProperties($this->getValues())->store();
            $this->redirectOnSuccess('The controller has successfully been stored');
        } else {
            Controller::create($this->getValues())->store($this->db);
            $this->redirectOnSuccess('A new controller has successfully been created');
        }
    }

    public function getObject()
    {
        return $this->object;
    }

    public function loadObject($id)
    {
        $this->object = Controller::load($id, $this->db);
        $this->addHidden('id');
        $props = $this->object->getProperties();
        $props['ip_address'] = long2ip($props['ip_address']);
        $this->setDefaults($props);
        return $this;
    }

    public function setDb($db)
    {
        $this->db = $db;
        return $this;
    }
}
