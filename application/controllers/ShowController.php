<?php

use Icinga\Module\Lynxtechnik\ActionController;
use Icinga\Module\Lynxtechnik\Db;
use Icinga\Module\Lynxtechnik\Device;

class Lynxtechnik_ShowController extends ActionController
{
    public function oldoverviewAction()
    {
        $this->view->title = 'Lynx Devices';
        $this->view->devices = array();
        $ips = array(
            '192.168.56.28',
        );
        foreach ($ips as $ip) {
            $this->view->devices[$ip] = new Device('192.168.56.28', 'public');
        }
    }

    public function overviewAction()
    {
        $this->view->title = 'Lynx Devices';
        $this->view->devices = $this->db()->fetchOverview();
    }

    public function stackAction()
    {
        $ip = $this->_getParam('ip');
        $this->setAutorefreshInterval(15);
        $this->view->title = 'Lynx Devices';
//        $this->view->title = 'Lynx Device: ' . $ip;
//        $this->view->devices = $this->db()->fetchDevicesByIp(preg_split('~,~', $ip));
        $this->view->devices = $this->db()->fetchAllDevices();
    }
}
