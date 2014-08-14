<?php

use Icinga\Module\Lynxtechnik\ActionController;
use Icinga\Module\Lynxtechnik\Device;

class Lynxtechnik_ListController extends ActionController
{
    public function templatesAction()
    {
        $this->setIcingaTabs()->activate('templates');
        $this->view->list = $this->db()->fetchTemplates();
    }

    public function hostsAction()
    {
        $this->setIcingaTabs()->activate('hosts');
        $this->view->list = $this->db()->fetchHosts();
    }

    public function servicesAction()
    {
        $this->setIcingaTabs()->activate('services');
        $this->view->list = $this->db()->fetchServices();
    }

    public function roomsAction()
    {
        $this->setDatacenterTabs()->activate('rooms');
        $this->view->list = $this->db()->fetchRooms();
    }

    public function racksAction()
    {
        $this->setDatacenterTabs()->activate('racks');
        $this->view->list = $this->db()->fetchRacks();
    }

    public function controllersAction()
    {
        $this->setDatacenterTabs()->activate('controllers');
        $this->view->list = $this->db()->fetchControllers();
    }
}
