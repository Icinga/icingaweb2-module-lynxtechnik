ALTER TABLE lynx_icinga_service_modules
  DROP FOREIGN KEY lynx_service_modules_module,
  DROP FOREIGN KEY lynx_service_modules_service;

ALTER TABLE lynx_icinga_service_modules
  ADD CONSTRAINT lynx_service_modules_service
    FOREIGN KEY service (service_id)
    REFERENCES lynx_icinga_service (id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT lynx_service_modules_module
    FOREIGN KEY module (module_id)
    REFERENCES lynx_module (id)
    ON DELETE CASCADE
    ON UPDATE CASCADE;

ALTER TABLE lynx_icinga_host
  MODIFY template_id INT(10) UNSIGNED DEFAULT NULL COMMENT 'Icinga template reference',
  ADD UNIQUE INDEX host_name (host_name);

ALTER TABLE lynx_icinga_service
  MODIFY template_id INT(10) UNSIGNED DEFAULT NULL COMMENT 'Icinga template reference',
  ADD UNIQUE INDEX service_description (service_description);

