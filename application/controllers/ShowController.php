<?php

use Icinga\Module\Lynxtechnik\ActionController;
use Icinga\Module\Lynxtechnik\Db;
use Icinga\Module\Lynxtechnik\Device;
use Icinga\Web\Widget;

class Lynxtechnik_ShowController extends ActionController
{
    protected function prepareFilterArray($array, $prefix)
    {
        $new = array();
        foreach ($array as $key => $val) {
            $new[$prefix . $key] = $val;
        }
        return $new;
    }

    public function stackAction()
    {
        $this->setAutorefreshInterval(15);
        $this->view->title = 'Lynx Devices';
        $this->view->tabs = Widget::create('tabs')->add('lynx', array(
            'label' => $this->view->title,
            'url'   => $this->_request->getUrl()
        ));
        $this->view->small = $this->params->get('small');
        $this->view->tabs->activate('lynx');

        $db = $this->db();
        $this->view->rack_list    = $db->listRackControllers();
        $this->view->service_list = $db->listServices();

        $this->view->filter  = $this->_request->get('filter');
        $this->view->devices = $db->fetchDevices($this->view->filter);
    }
}
