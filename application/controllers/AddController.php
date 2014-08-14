<?php

use Icinga\Module\Lynxtechnik\ActionController;

class Lynxtechnik_AddController extends ActionController
{
    public function __moduleAction()
    {
        $this->view->title = 'Lynx Devices';
        $this->view->devices = $this->db()->fetchOverview();
    }

    public function templateAction()
    {
        $this->view->title = $this->translate('Add new Icinga Template');
        $this->view->form = $this->loadForm('icingaTemplate')
            ->setDb($this->db())
            ->setSuccessUrl('lynxtechnik/list/templates')
            ->handleRequest();
        $this->render('form');
    }

    public function hostAction()
    {
        $this->view->title = $this->translate('Add new Icinga Host');
        $this->view->form = $this->loadForm('icingaHost')
            ->setDb($this->db())
            ->setSuccessUrl('lynxtechnik/list/hosts')
            ->handleRequest();
        $this->render('form');
    }

    public function serviceAction()
    {
        $this->view->title = $this->translate('Add new Icinga Service');
        $this->view->form = $this->loadForm('icingaService')
            ->setDb($this->db())
            ->setSuccessUrl('lynxtechnik/list/services')
            ->handleRequest();
        $this->render('form');
    }

    public function roomAction()
    {
        $this->view->title = $this->translate('Add new room');
        $this->view->form = $this->loadForm('room')
            ->setDb($this->db())
            ->setSuccessUrl('lynxtechnik/list/rooms')
            ->handleRequest();
        $this->render('form');
    }

    public function rackAction()
    {
        $this->view->title = $this->translate('Add new rack');
        $this->view->form = $this->loadForm('rack')
            ->setDb($this->db())
            ->setSuccessUrl('lynxtechnik/list/racks')
            ->handleRequest();
        $this->render('form');
    }

    public function controllerAction()
    {
        $this->view->title = $this->translate('Add new controller');
        $this->view->form = $this->loadForm('controller')
            ->setDb($this->db())
            ->setSuccessUrl('lynxtechnik/list/controllers')
            ->handleRequest();
        $this->render('form');
    }
}

