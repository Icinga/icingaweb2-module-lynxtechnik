<?php

namespace Icinga\Module\Lynxtechnik\Forms;

use Icinga\Module\Lynxtechnik\IcingaTemplate;
use Icinga\Module\Lynxtechnik\Web\Form\QuickForm;

class IcingaTemplateForm extends QuickForm
{
    protected $db;

    protected $object;

    public function setup()
    {
        $this->addElement('text', 'name', array(
            'label' => $this->translate('Icinga Template Name'),
            'required' => true,
            'description' => $this->translate('Template name as defined in your Icinga Config')
        ));
        $this->addElement('text', 'title', array(
            'label' => $this->translate('Title'),
            'required' => true,
            'description' => $this->translate('Template title that should be shown in LYNX Technik frontend')
        ));
        $this->addElement('select', 'type', array(
            'label' => $this->translate('Type'),
            'multiOptions' => array(
                null      => $this->translate(' - please choose - '),
                'host'    => $this->translate('Host'),
                'service' => $this->translate('Service'),
            ),
            'required' => true,
            'description' => $this->translate('Icinga template type, host or service template')
        ));
        $this->addElement('submit', $this->translate('Store'));
    }

    public function onSuccess()
    {
        if ($this->object) {
            $this->object->setProperties($this->getValues())->store();
            $this->redirectOnSuccess('The Icinga Template has successfully been stored');
        } else {
            IcingaTemplate::create($this->getValues())->store($this->db);
            $this->redirectOnSuccess('A new Icinga Template has successfully been created');
        }
    }

    public function getObject()
    {
        return $this->object;
    }

    public function loadObject($id)
    {
        $this->object = IcingaTemplate::load($id, $this->db);
        $this->addHidden('id');
        $this->setDefaults($this->object->getProperties());
        return $this;
    }

    public function setDb($db)
    {
        $this->db = $db;
        return $this;
    }
}
