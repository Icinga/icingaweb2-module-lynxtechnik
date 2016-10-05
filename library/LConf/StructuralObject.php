<?php

class LConf_StructuralObject extends LConf_Object
{
    protected $class_name = 'StructuralObject';
    protected $key = 'ou';

    protected $attribute_map = array(
//        'email'                              => 'email',
//        'pager'                              => 'pager',
//^        'alias'                              => 'alias',
/*        'contactservicenotificationperiod'   => 'service_notification_period',
        'contacthostnotificationperiod'      => 'host_notification_period',
        'contactservicenotificationoptions'  => 'service_notification_options',
        'contacthostnotificationoptions'     => 'host_notification_options',
        'contactservicenotificationcommands' => 'service_notification_commands',
        'contacthostnotificationcommands'    => 'host_notification_commands',
        'contactservicenotificationsenabled' => 'service_notifications_enabled',
        'contacthostnotificationsenabled'    => 'host_notifications_enabled',
        'contactcansubmitcommands'           => 'can_submit_commands',
        'contactgroups'                      => 'contactgroups',*/
//        'address'                                      => 'address',
        'parent'                                       => 'parents',
        'hostcustomvar'                                => 'PLACEHOLDER',
        'hostcontacts'                                 => 'contacts',
        'hostcontactgroups'                            => 'contact_groups',
        'hostnotificationoptions'                      => 'notification_options',
        'hostnotificationinterval'                     => 'notification_interval',
        'hostnotificationperiod'                       => 'notification_period',
        'hostcheckperiod'                              => 'check_period',
        'hostcheckinterval'                            => 'check_interval',
        'hostcheckretryinterval'                       => 'retry_interval',
        'hostcheckmaxcheckattempts'                    => 'max_check_attempts',
        'hostprocessperfdata'                          => 'process_perf_data',
        'hostactivechecksenabled'                      => 'active_checks_enabled',
        'hostpassivechecksenabled'                     => 'passive_checks_enabled',
        'hostflapdetectionenabled'                     => 'flap_detection_enabled',
        'hostflapdetectionoptions'                     => 'flap_detection_options',
        'hosteventhandler'                             => 'event_handler',
        'hostnotesurl'                                 => 'notes_url',
        'hostactionurl'                                => 'action_url',
        'hostgroups'                                   => 'hostgroups',
        'hostdependency'                               => 'PLACEHOLDER',
        'hostdependencyexecutionfailurecriteria'       => 'execution_failure_criteria',
        'hostdependencynotificationfailurecriteria'    => 'notification_failure_criteria',
        'hostdependencyinheritsparent'                 => 'inherits_parent',
        'hostnotificationsenabled'                     => 'notifications_enabled',
        'hostfreshnessthreshold'                       => 'freshness_threshold',
        'hostcheckfreshness'                           => 'check_freshness',
        'hostcheckcommand'                             => 'check_command',
        'hostdisable'                                  => '',

    );

}
