<?php

namespace Icinga\Module\Lynxtechnik\ProvidedHook\Monitoring;

use Icinga\Application\Config;
use Icinga\Module\Monitoring\Hook\ServiceActionsHook;
use Icinga\Module\Monitoring\Object\Service;
use Icinga\Web\Url;

class ServiceActions extends ServiceActionsHook
{
    public function getActionsForService(Service $service)
    {
        if (array_key_exists('lynx_service_id', $service->customvars)) {
            return array(
                'Show stack' => Url::fromPath(
                    'lynxtechnik/show/stack',
                    array('filter' => $service->customvars['lynx_service_id'])
                )
            );
        } else {
            return array();
        }
    }
}
