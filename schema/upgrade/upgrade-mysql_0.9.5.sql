
ALTER TABLE lynx_module
    ADD prefix VARCHAR(16) NOT NULL COMMENT 'SNMP OID prefix' AFTER type_name,
    ADD uptime INT(10) UNSIGNED NOT NULL COMMENT 'Module uptime' after status_color_rgb;

ALTER TABLE lynx_controller ADD last_discovery DATETIME DEFAULT NULL AFTER community;

ALTER TABLE lynx_module MODIFY module_type ENUM('controller', 'expander', 'module', 'stack','rack','slot') NOT NULL
    COMMENT 'Shows whether this module has a special role (e.g. controller)';
UPDATE lynx_module SET module_type = 'controller' WHERE module_type = 'stack';
UPDATE lynx_module SET module_type = 'expander' WHERE module_type = 'rack';
UPDATE lynx_module SET module_type = 'module' WHERE module_type = 'slot';
ALTER TABLE lynx_module MODIFY module_type ENUM('controller', 'expander', 'module') NOT NULL
    COMMENT 'Shows whether this module has a special role (e.g. controller)';

