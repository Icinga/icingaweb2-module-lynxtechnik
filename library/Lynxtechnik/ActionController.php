<?php

namespace Icinga\Module\Lynxtechnik;

use Icinga\Web\Controller\ModuleActionController;
use Icinga\Web\Widget;
use Icinga\Module\Lynxtechnik\Db;
use Icinga\Module\Lynxtechnik\Web\Form\FormLoader;

abstract class ActionController extends ModuleActionController
{
    protected $db;

    public function loadForm($name)
    {
        $loader = new FormLoader();
        return $loader->load($name, $this->Module());
    }

    protected function setIcingaTabs()
    {
        $this->view->tabs = Widget::create('tabs')->add('services', array(
            'title' => $this->translate('Services'),
            'url'   => 'lynxtechnik/list/services')
        )->add('hosts', array(
            'title' => $this->translate('Hosts'),
            'url'   => 'lynxtechnik/list/hosts')
        )->add('templates', array(
            'title' => $this->translate('Templates'),
            'url'   => 'lynxtechnik/list/templates')
        )->add('config', array(
            'title' => $this->translate('Config'),
            'url'   => 'lynxtechnik/icinga/config')
        );
        return $this->view->tabs;
    }

    protected function setDatacenterTabs()
    {
        $this->view->tabs = Widget::create('tabs')->add('rooms', array(
            'title' => $this->translate('DC rooms'),
            'url'   => 'lynxtechnik/list/rooms')
        )->add('racks', array(
            'title' => $this->translate('Racks'),
            'url'   => 'lynxtechnik/list/racks')
        )/*->add('frames', array(
            'title' => $this->translate('Frames'),
            'url'   => 'lynxtechnik/list/frames')
        )*/->add('controllers', array(
            'title' => $this->translate('Controllers'),
            'url'   => 'lynxtechnik/list/controllers')
        );
        return $this->view->tabs;
    }

    protected function db()
    {
        if ($this->db === null) {
            $this->db = Db::fromResourceName($this->Config()->db->resource);
        }

        return $this->db;
    }
}
