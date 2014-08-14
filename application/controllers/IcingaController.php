<?php

use Icinga\Module\Lynxtechnik\ActionController;
use Icinga\Module\Lynxtechnik\IcingaConfig;

class Lynxtechnik_IcingaController extends ActionController
{
    public function configAction()
    {
        $this->setIcingaTabs()->activate('config');
        $this->view->title = 'Lynx Icinga Config';
        $this->view->config = IcingaConfig::fromDb($this->db());
    }
}
