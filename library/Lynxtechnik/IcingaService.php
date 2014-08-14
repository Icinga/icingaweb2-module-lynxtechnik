<?php

namespace Icinga\Module\Lynxtechnik;

use Icinga\Module\Lynxtechnik\Data\Db\DbObject;

class IcingaService extends DbObject
{
    protected $table = 'lynx_icinga_service';

    protected $keyName = 'id';

    protected $autoincKeyName = 'id';

    protected $defaultProperties = array(
        'id'                  => null,
        'host_id'             => null,
        'template_id'         => null,
        'service_description' => null,
    );

    protected $moduleIds;

    public function setModule_ids($ids)
    {
        if ($ids === $this->moduleIds) return;

        $this->moduleIds = $ids;
        $this->hasBeenModified = true;
    }

    public function beforeStore()
    {
        $this->db->beginTransaction();
    }

    public function onStore()
    {
        $current = $this->getCurrentServiceModules();
        $ids = $this->getModule_ids();

        foreach ($ids as $id) {
            if (! in_array($id, $current)) {
                $this->db->insert('lynx_icinga_service_modules', array(
                    'service_id' => $this->id,
                    'module_id'  => $id
                ));
            }
        }

        foreach ($current as $id) {
            if (! in_array($id, $ids)) {
                $this->db->delete(
                    'lynx_icinga_service_modules',
                    sprintf('service_id = %d AND module_id = %d', $this->id, $id)
                );
            }
        }
        $this->db->commit();
    }

    protected function getCurrentServiceModules()
    {
        if (! $this->hasBeenLoadedFromDb()) {
            return array();
        }
        $select = $this->db->select()->from('lynx_icinga_service_modules', array(
            'module_id'
        ))->where('service_id = ?', $this->id)->order('module_id ASC');
        return $this->db->fetchCol($select);
    }

    public function getModule_ids()
    {
        if ($this->moduleIds === null) {
            $this->moduleIds = $this->getCurrentServiceModules();
        }
        return $this->moduleIds;
    }

}
