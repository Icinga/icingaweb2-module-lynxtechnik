<?php

namespace Icinga\Module\Lynxtechnik\Forms;

use Icinga\Module\Lynxtechnik\IcingaService;
use Icinga\Module\Lynxtechnik\LConfSync;
use Icinga\Module\Lynxtechnik\Web\Form\QuickForm;

class IcingaServiceForm extends QuickForm
{
    protected $db;

    protected $object;

    protected $lconf;

    public function setup()
    {
        $this->addElement('text', 'service_description', array(
            'label'       => $this->translate('Icinga service description'),
            'required'    => true,
            'description' => $this->translate('Service description for the LYNX Technik Icinga service you are going to create')
        ));
        $this->addElement('select', 'host_id', array(
            'label'       => $this->translate('Host'),
            'required'    => true,
            'description' => $this->translate('Icinga Host')
        ));
        $this->addElement('select', 'template_id', array(
            'label'       => $this->translate('Template'),
            'required'    => true,
            'description' => $this->translate('Icinga host template')
        ));
        $this->addElement('multiselect', 'module_ids', array(
            'label'       => $this->translate('LYNX Modules'),
            'required'    => true,
            'size'        => 14,
            'style'       => 'width: 25em;',
            'description' => $this->translate('LYNX Technik modules to be associated with this Icinga service')
        ));
        $this->addElement('submit', $this->translate('Store'));
    }

    public function onSuccess()
    {
        if ($this->object) {
            $this->object->setProperties($this->getValues())->store();
            $this->syncLConfIfEnabled();
            $this->redirectOnSuccess('The Icinga Service has successfully been stored');
        } else {
            IcingaService::create($this->getValues())->store($this->db);
            $this->syncLConfIfEnabled();
            $this->redirectOnSuccess('A new Icinga Service has successfully been created');
        }
    }

    public function getObject()
    {
        return $this->object;
    }

    public function loadObject($id)
    {
        $this->object = IcingaService::load($id, $this->db);
        $this->addHidden('id');
        $this->setDefaults(
            $this->object->getProperties()
            + array('module_ids' => $this->object->module_ids)
        );
        return $this;
    }

    public function setDb($db)
    {
        $this->db = $db;
        $this->getElement('host_id')->setMultiOptions(
            array(null => $this->translate('- please choose -')) + $db->enumHosts()
        );
        $this->getElement('template_id')->setMultiOptions(
            array(null => $this->translate('- please choose -')) + $db->enumServiceTemplates()
        );
        $this->getElement('module_ids')->setMultiOptions($db->enumModules());
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

    protected function syncLConfIfEnabled()
    {
        if ($this->lconf !== null && $this->lconf->isEnabled()) {
            $this->lconf->synchronize();
        }
    }
}
