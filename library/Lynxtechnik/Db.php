<?php

namespace Icinga\Module\Lynxtechnik;

use Icinga\Data\Db\DbConnection;

class Db extends DbConnection
{
    protected $modules = array();

    protected function db()
    {
        return $this->getDbAdapter();
    }

    public function fetchTemplates()
    {
        $select = $this->db()->select()->from('lynx_icinga_template', array(
            'id',
            'name',
            'title',
            'type'
        ))->order('title ASC');
        return $this->db()->fetchAll($select);
    }

    public function fetchHosts()
    {
        $select = $this->db()->select()->from('lynx_icinga_host', array(
            'id',
            'template_id',
            'host_name'
        ))->order('host_name ASC');
        return $this->db()->fetchAll($select);
    }

    public function listServices()
    {
        $select = $this->db()->select()->from('lynx_icinga_service', array(
            'id',
            'service_description'
        ))->order('service_description');
        return $this->db()->fetchPairs($select);
    }

    public function listRackControllers()
    {
        $select = $this->db()->select()->from('lynx_module', array(
            'INET_NTOA(controller_ip)',
            'display_name'
        ))->where('module_type IN (?)', array('controller'))->order('display_name');
        return $this->db()->fetchPairs($select);
    }

    public function fetchServices()
    {
        $select = $this->db()->select()->from(
            array('s' => 'lynx_icinga_service'),
            array(
                'id',
                'host_id',
                'template_id',
                'service_description'
            )
        )->join(
            array('h' => 'lynx_icinga_host'),
            'h.id = s.host_id',
            array(
                'host_name'
            )
        )->joinLeft(
            array('t' => 'lynx_icinga_template'),
            't.id = s.template_id',
            array(
                'template_name'  => 'name',
                'template_title' => 'title'
            )
        )->joinLeft(
            array('sm' => 'lynx_icinga_service_modules'),
            'sm.service_id = s.id',
            array()
        )->joinLeft(
            array('m' => 'lynx_module'),
            'sm.module_id = m.id',
            array(
                'cnt_ok'       => 'SUM(CASE WHEN m.status_color = 2 THEN 1 ELSE 0 END)',
                'cnt_warning'  => 'SUM(CASE WHEN m.status_color = 3 THEN 1 ELSE 0 END)',
                'cnt_critical' => 'SUM(CASE WHEN m.status_color = 4 THEN 1 ELSE 0 END)',
                'cnt_unknown'  => 'SUM(CASE WHEN m.status_color = 0 THEN 1 ELSE 0 END)',
                'modules' => 'GROUP_CONCAT(m.display_name)',
                'module_ids' => 'GROUP_CONCAT(m.id)'
            )
        )->group('s.id')->order('service_description ASC');
        return $this->db()->fetchAll($select);
    }

    public function fetchControllers()
    {
        $select = $this->db()->select()->from('lynx_controller', array(
            'id',
            'frame_id',
            'ip_address',
            'community',
            'UNIX_TIMESTAMP(last_discovery) AS last_discovery',
        ))->order('ip_address ASC');
        return $this->db()->fetchAll($select);
    }

    public function fetchRooms()
    {
        $select = $this->db()->select()->from('lynx_room', array(
            'id',
            'display_name',
        ))->order('display_name ASC');
        return $this->db()->fetchAll($select);
    }

    public function enumRooms()
    {
        $select = $this->db()->select()->from('lynx_room', array(
            'id',
            'display_name',
        ))->order('display_name ASC');
        return $this->db()->fetchPairs($select);
    }

    public function enumModules()
    {
        $select = $this->db()->select()->from('lynx_module', array(
            'id',
            'position',
            'position_text',
            'controller_ip',
            'display_name'
        ))->order('controller_ip')->order('position');
        
        $data = $this->db()->fetchAll($select);
        $result = array();
        $oldPos = null;
        foreach ($data as $row) {
            if ($oldPos === null || $oldPos > $row->position) {
                $cur = &$result[long2ip($row->controller_ip)];
                $cur = array();
            }
            $oldPos = $row->position;
            $cur[$row->id] = $row->position_text . ': ' . $row->display_name;
        }
        return $result;
    }

    public function enumHosts()
    {
        $select = $this->db()->select()->from('lynx_icinga_host', array(
            'id',
            'host_name',
        ))->order('host_name ASC');
        return $this->db()->fetchPairs($select);
    }

    public function enumHostTemplates()
    {
        $select = $this->db()->select()->from('lynx_icinga_template', array(
            'id',
            'title',
        ))->where('type = ?', 'host')->order('title ASC');
        return $this->db()->fetchPairs($select);
    }

    public function enumServiceTemplates()
    {
        $select = $this->db()->select()->from('lynx_icinga_template', array(
            'id',
            'title',
        ))->where('type = ?', 'service')->order('title ASC');
        return $this->db()->fetchPairs($select);
    }

    public function fetchRacks()
    {
        $select = $this->db()->select()->from(
            array('ra' => 'lynx_rack'),
            array(
                'id',
                'display_name',
            )
        )->join(
            array('ro' => 'lynx_room'),
            'ro.id = ra.room_id',
            array(
                'room' => 'ro.display_name'
            )
        )->order('display_name ASC');
        return $this->db()->fetchAll($select);
    }

    protected function deviceListColumns()
    {
        return array(
            'id',
            'address' => 'controller_ip',
            'position',
            'position_text',
            'display_name',
            'type' => 'module_type',
            'version',
            'type_code',
            'type_name',
            'status_text',
            'status_color',
            'status_color_rgb'
        );
    }

    protected function selectDevices()
    {
        return $this->db()->select()->from(
            array('m' => 'lynx_module'),
            $this->deviceListColumns()
        )->order('controller_ip ASC')->order('position ASC');
    }

    public function fetchDevices($filter = null)
    {
        $select = $this->selectDevices();

        if ($filter !== null) {

            if (ctype_digit($filter)) {

                $select->join(
                    array('sm' => 'lynx_icinga_service_modules'),
                    'sm.module_id = m.id',
                    array()
                )->join(
                    array('s' => 'lynx_icinga_service'),
                    'sm.service_id = s.id',
                    array()
                )->where('s.id = ?', $filter);

            } else {

                $ip = ip2long($filter);
                if ($ip !== false) {
                    $select->where('controller_ip = ?', $ip);
                }
            }
        }
        return $this->db()->fetchAll($select);
    }

}
