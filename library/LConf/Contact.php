<?php

class LConf_Contact extends LConf_Object
{
    protected $class_name = 'Contact';
    protected $attribute_map = array(
        'email'                              => 'email',
        'pager'                              => 'pager',
        'alias'                              => 'alias',
        'contactservicenotificationperiod'   => 'service_notification_period',
        'contacthostnotificationperiod'      => 'host_notification_period',
        'contactservicenotificationoptions'  => 'service_notification_options',
        'contacthostnotificationoptions'     => 'host_notification_options',
        'contactservicenotificationcommands' => 'service_notification_commands',
        'contacthostnotificationcommands'    => 'host_notification_commands',
        'contactservicenotificationsenabled' => 'service_notifications_enabled',
        'contacthostnotificationsenabled'    => 'host_notifications_enabled',
        'contactcansubmitcommands'           => 'can_submit_commands',
        'contactgroups'                      => 'contactgroups',
    );

}

