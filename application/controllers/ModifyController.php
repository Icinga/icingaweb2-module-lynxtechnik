<?php

use Icinga\Module\Lynxtechnik\ActionController;

class Lynxtechnik_ModifyController extends ActionController
{
    public function moduleAction()
    {
        $this->view->title = 'Lynx Devices';
        $this->view->devices = $this->db()->fetchOverview();
    }

    public function templateAction()
    {
        $this->view->title = $this->translate('Modify Icinga Template');

        $form = $this->loadForm('icingaTemplate')
            ->setDb($this->db())
            ->loadObject($this->params->get('id'))
            ->setSuccessUrl('lynxtechnik/list/templates')
            ->handleRequest();

        $this->view->remove = $this->loadForm('remove');
        if ($this->view->remove->hasBeenSent()) {
            if ($form->getObject()->delete()) {
                $form->notifySuccess('Template has been deleted');   
            }
            $this->redirectNow('lynxtechnik/list/templates');
        }

        $this->view->form = $form;
        $this->render('form');
    }

    public function hostAction()
    {
        $this->view->title = $this->translate('Modify Icinga Host');

        $form = $this->loadForm('icingaHost')
            ->setDb($this->db())
            ->loadObject($this->params->get('id'))
            ->setSuccessUrl('lynxtechnik/list/hosts')
            ->handleRequest();

        $this->view->remove = $this->loadForm('remove');
        if ($this->view->remove->hasBeenSent()) {
            if ($form->getObject()->delete()) {
                $form->notifySuccess('Host has been deleted');   
            }
            $this->redirectNow('lynxtechnik/list/hosts');
        }

        $this->view->form = $form;
        $this->render('form');
    }

    public function serviceAction()
    {
        $this->view->title = $this->translate('Modify Icinga Service');

        $form = $this->loadForm('icingaService')
            ->setDb($this->db())
            ->loadObject($this->params->get('id'))
            ->setSuccessUrl('lynxtechnik/list/services')
            ->handleRequest();

        $this->view->remove = $this->loadForm('remove');
        if ($this->view->remove->hasBeenSent()) {
            if ($form->getObject()->delete()) {
                $form->notifySuccess('Service has been deleted');   
            }
            $this->redirectNow('lynxtechnik/list/services');
        }

        $this->view->form = $form;
        $this->render('form');
    }

    public function roomAction()
    {
        $this->view->title = $this->translate('Modify datacenter room');

        $form = $this->loadForm('room')
            ->setDb($this->db())
            ->loadObject($this->params->get('id'))
            ->setSuccessUrl('lynxtechnik/list/rooms')
            ->handleRequest();

        $this->view->remove = $this->loadForm('remove');
        if ($this->view->remove->hasBeenSent()) {
            if ($form->getObject()->delete()) {
                $form->notifySuccess('Room has been deleted');   
            }
            $this->redirectNow('lynxtechnik/list/rooms');
        }

        $this->view->form = $form;
        $this->render('form');
    }

    public function rackAction()
    {
        $this->view->title = $this->translate('Modify datacenter rack');

        $form = $this->loadForm('rack')
            ->setDb($this->db())
            ->loadObject($this->params->get('id'))
            ->setSuccessUrl('lynxtechnik/list/racks')
            ->handleRequest();

        $this->view->remove = $this->loadForm('remove');
        if ($this->view->remove->hasBeenSent()) {
            if ($form->getObject()->delete()) {
                $form->notifySuccess('Rack has been deleted');   
            }
            $this->redirectNow('lynxtechnik/list/racks');
        }

        $this->view->form = $form;
        $this->render('form');
    }

    public function controllerAction()
    {
        $this->view->title = $this->translate('Modify LYNX Technik controller');

        $form = $this->loadForm('controller')
            ->setDb($this->db())
            ->loadObject($this->params->get('id'))
            ->setSuccessUrl('lynxtechnik/list/controllers')
            ->handleRequest();

        $this->view->remove = $this->loadForm('remove');
        if ($this->view->remove->hasBeenSent()) {
            if ($form->getObject()->delete()) {
                $form->notifySuccess('Controller has been deleted');   
            }
            $this->redirectNow('lynxtechnik/list/controllers');
        }

        $this->view->form = $form;
        $this->render('form');
    }
}
