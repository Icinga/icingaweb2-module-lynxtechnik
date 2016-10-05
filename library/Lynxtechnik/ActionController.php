<?php

namespace Icinga\Module\Lynxtechnik;

use Icinga\Application\Icinga;
use Icinga\Module\Lynxtechnik\Db;
use Icinga\Module\Lynxtechnik\LConfSync;
use Icinga\Module\Lynxtechnik\Web\Form\FormLoader;
use Icinga\Web\Controller;
use Icinga\Web\Widget;

abstract class ActionController extends Controller
{
    protected $db;

    private $lconf;

    protected $forcedMonitoring = false;

    public function init()
    {
        $m = Icinga::app()->getModuleManager();
        if (! $m->hasLoaded('monitoring') && $m->hasInstalled('monitoring')) {
            $m->loadModule('monitoring');
        }
    }

    public function loadForm($name)
    {
        $loader = new FormLoader();
        return $loader->load($name, $this->Module());
    }

    protected function setIcingaTabs()
    {
        $this->view->tabs = Widget::create('tabs')->add('services', array(
            'label' => $this->translate('Services'),
            'url'   => 'lynxtechnik/list/services')
        )->add('hosts', array(
            'label' => $this->translate('Hosts'),
            'url'   => 'lynxtechnik/list/hosts')
        )->add('controllers', array(
            'label' => $this->translate('Controllers'),
            'url'   => 'lynxtechnik/list/controllers')
        );
        if (! $this->lconf()->isEnabled()) {
            $this->view->tabs->add('templates', array(
            'label' => $this->translate('Templates'),
            'url'   => 'lynxtechnik/list/templates')
        )->add('config', array(
            'label' => $this->translate('Config'),
            'url'   => 'lynxtechnik/icinga/config')
        );
        }
        return $this->view->tabs;
    }

    protected function setDatacenterTabs()
    {
        $this->view->tabs = Widget::create('tabs')->add('rooms', array(
            'label' => $this->translate('DC rooms'),
            'url'   => 'lynxtechnik/list/rooms')
        )->add('racks', array(
            'label' => $this->translate('Racks'),
            'url'   => 'lynxtechnik/list/racks')
        )/*->add('frames', array(
            'label' => $this->translate('Frames'),
            'url'   => 'lynxtechnik/list/frames')
        )->add('controllers', array(
            'label' => $this->translate('Controllers'),
            'url'   => 'lynxtechnik/list/controllers')
        )*/;
        return $this->view->tabs;
    }

    protected function lconf()
    {
        if ($this->lconf === null) {
            $this->lconf = new LConfSync(
                $this->Config()->getSection('lconf'), $this->db()
            );
        }
        return $this->lconf;
    }

    protected function db()
    {
        if ($this->db === null) {
            $this->db = Db::fromResourceName($this->Config()->get('db', 'resource'));
        }

        return $this->db;
    }
}
