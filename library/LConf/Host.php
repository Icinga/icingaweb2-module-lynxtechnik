<?php

class LConf_Host extends LConf_Object
{
    protected $class_name = 'Host';
    protected $_service_details = array();
    protected $attribute_map = array(
        'alias'                                        => 'alias',
        'address'                                      => 'address',
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

    // EXPERIMENTAL, subject to change:
    public function hasServiceDetails()
    {
        return ! empty($this->_service_details);
    }

    public function addServiceDetails($name, $params)
    {
        $this->_service_details[$name] = $params;
        return $this;
    }

    public function asParent()
    {
        return $this->getDirectory()->subDirectory($this->getName(), 'cn');
    }

    public function getServiceDetails()
    {
        return $this->_service_details;
    }
    // END of EXPERIMENTAL section
}
