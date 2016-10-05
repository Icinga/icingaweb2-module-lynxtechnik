<?php

namespace Icinga\Module\Lynxtechnik\Forms;

use Icinga\Module\Lynxtechnik\IcingaHost;
use Icinga\Module\Lynxtechnik\LConfSync;
use Icinga\Module\Lynxtechnik\Web\Form\QuickForm;

class IcingaHostForm extends QuickForm
{
    protected $db;

    protected $object;

    protected $lconf;

    public function setup()
    {
        $this->addElement('text', 'host_name', array(
            'label' => $this->translate('Icinga hostname'),
            'required' => true,
            'description' => $this->translate('Hostname for the Icinga host you are going to create for your LYNX Technik services')
        ));
        $this->addElement('select', 'template_id', array(
            'label' => $this->translate('Template'),
            'required' => true,
            'description' => $this->translate('Icinga host template')
        ));
        $this->addElement('submit', $this->translate('Store'));
    }

    public function onSuccess()
    {
        if ($this->object) {
            $this->object->setProperties($this->getValues())->store();
            $this->redirectOnSuccess('The Icinga Host has successfully been stored');
        } else {
            IcingaHost::create($this->getValues())->store($this->db);
            $this->redirectOnSuccess('A new Icinga Host has successfully been created');
        }
    }

    public function getObject()
    {
        return $this->object;
    }

    public function loadObject($id)
    {
        $this->object = IcingaHost::load($id, $this->db);
        $this->addHidden('id');
        $this->setDefaults($this->object->getProperties());
        return $this;
    }

    public function setLConfSync(LConfSync $lconf)
    {
        $this->lconf = $lconf;
        if ($lconf->isEnabled()) {
            $this->removeElement('template_id');
        }
        return $this;
    }

    public function setDb($db)
    {
        $this->db = $db;
        $this->getElement('template_id')->setMultiOptions(
            array(null => $this->translate('- please choose -')) + $db->enumHostTemplates()
        );
        return $this;
    }
}
