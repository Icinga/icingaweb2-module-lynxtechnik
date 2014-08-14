<?php

namespace Icinga\Module\Lynxtechnik\Forms;

use Icinga\Module\Lynxtechnik\Room;
use Icinga\Module\Lynxtechnik\Web\Form\QuickForm;

class RoomForm extends QuickForm
{
    protected $db;

    protected $object;

    public function setup()
    {
        $this->addElement('text', 'display_name', array(
            'label' => $this->translate('Room name'),
            'required' => true,
            'description' => $this->translate('Every datacenter room should have a unique name')
        ));
        $this->addElement('submit', $this->translate('Store'));
    }

    public function onSuccess()
    {
        if ($this->object) {
            $this->object->setProperties($this->getValues())->store();
            $this->redirectOnSuccess('The room has successfully been stored');
        } else {
            Room::create($this->getValues())->store($this->db);
            $this->redirectOnSuccess('A new room has successfully been created');
        }
    }

    public function getObject()
    {
        return $this->object;
    }

    public function loadObject($id)
    {
        $this->addHidden('id');
        $this->object = Room::load($id, $this->db);
        $this->setDefaults($this->object->getProperties());
        return $this;
    }

    public function setDb($db)
    {
        $this->db = $db;
        return $this;
    }
}
