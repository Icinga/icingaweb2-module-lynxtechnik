<?php

namespace Icinga\Module\Lynxtechnik\Forms;

use Icinga\Module\Lynxtechnik\Rack;
use Icinga\Module\Lynxtechnik\Web\Form\QuickForm;

class RackForm extends QuickForm
{
    protected $db;

    protected $object;

    public function setup()
    {
        $this->addElement('text', 'display_name', array(
            'label' => $this->translate('Rack name'),
            'required' => true,
            'description' => $this->translate('Every datacenter rack should have a unique name')
        ));
        $this->addElement('select', 'room_id', array(
            'label' => $this->translate('Room'),
            'required' => true,
            'description' => $this->translate('The room where we can find this rack')
        ));
        $this->addElement('submit', $this->translate('Store'));
    }

    public function onSuccess()
    {
        if ($this->object) {
            $this->object->setProperties($this->getValues())->store();
            $this->redirectOnSuccess('The rack has successfully been stored');
        } else {
            Rack::create($this->getValues())->store($this->db);
            $this->redirectOnSuccess('A new rack has successfully been created');
        }
    }

    public function getObject()
    {
        return $this->object;
    }

    public function loadObject($id)
    {
        $this->object = Rack::load($id, $this->db);
        $this->addHidden('id');
        $this->setDefaults($this->object->getProperties());
        return $this;
    }

    public function setDb($db)
    {
        $this->db = $db;
        $this->getElement('room_id')->setMultiOptions(
            array(null => $this->translate('- please choose -')) + $db->enumRooms()
        );
        return $this;
    }
}
