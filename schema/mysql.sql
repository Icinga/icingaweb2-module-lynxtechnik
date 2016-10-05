


SET sql_mode = 'STRICT_ALL_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION,PIPES_AS_CONCAT,ANSI_QUOTES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER';

CREATE TABLE lynx_room
(
  id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique datacenter id',
  display_name VARCHAR(64) NOT NULL COMMENT 'Datacenter room display name',

  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
  COMMENT='Datacenter rooms for LYNX Technik devices';

CREATE TABLE lynx_rack
(
  id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique datacenter rack id',
  room_id INT(10) UNSIGNED NOT NULL COMMENT 'Datacenter room reference',
  display_name VARCHAR(64) NOT NULL COMMENT 'Datacenter rack display name',

  PRIMARY KEY (id),
  CONSTRAINT lynx_rack_room
    FOREIGN KEY room (room_id)
    REFERENCES lynx_room (id)
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8
  COMMENT='Datacenter racks for LYNX Technik devices';

CREATE TABLE lynx_frame
(
  id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique frame id',
  rack_id INT(10) UNSIGNED NOT NULL COMMENT 'Datacenter rack reference',
  position TINYINT(3) UNSIGNED NOT NULL
    COMMENT 'Relative position, rack unit offset starting from top',
  height TINYINT(3) UNSIGNED NOT NULL COMMENT 'Frame height in rack units',

  PRIMARY KEY (id),
  CONSTRAINT lynx_frame_rack FOREIGN KEY rack (rack_id)
    REFERENCES lynx_rack (id)
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8
  COMMENT='A LYNX Technik frame is a rack encosure';

CREATE TABLE lynx_module
(
  id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique module id',
  frame_id INT(10) NULL DEFAULT NULL COMMENT 'Frame reference',
  controller_ip INT(10) UNSIGNED NOT NULL
    COMMENT 'The last controller IP we got this modules information from',
  position INT(10) UNSIGNED NOT NULL
    COMMENT 'Binary LYNX Technik internal position (e.g. 1.0.0.0)',
  position_text VARCHAR(16) NOT NULL
    COMMENT 'Plaintext LYNX Technik internal position (e.g. 1.0.0.0)',
  display_name VARCHAR(255) NOT NULL COMMENT 'Module display name',
  module_type ENUM('controller', 'expander', 'module') NOT NULL
    COMMENT 'Shows whether this module has a special role (e.g. controller)',
  version VARCHAR(16) NOT NULL
    COMMENT 'This modules firmware version (e.g. 306.3.13)',
  type_code INT(10) UNSIGNED NOT NULL, -- TODO: this is always 0, do we really need this?
  type_name VARCHAR(255) NOT NULL
    COMMENT 'Plaintext module type (e.g. "PDX5312 SD Deembedder")',
  prefix VARCHAR(16) NOT NULL COMMENT 'SNMP OID prefix',
  status_text TEXT
    COMMENT 'Plaintext module status, mostly "OK" or an error message',
  status_color TINYINT(3) UNSIGNED NOT NULL
    COMMENT 'Numeric status color, currently 0-4',
  status_color_rgb VARCHAR(8) DEFAULT NULL
    COMMENT 'RGB status color code (e.g. #ff0000)',
  uptime INT(10) UNSIGNED NOT NULL COMMENT 'Module uptime',
  ctime DATETIME NOT NULL
    COMMENT 'Time this module got created',
  mtime TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    COMMENT 'This modules last modification time',

  PRIMARY KEY (id),
  KEY search_idx (frame_id, position),
  KEY module_type (module_type)
  -- ,
  -- KEY frame_id (frame_id),

  -- CONSTRAINT lynx_module_frame FOREIGN KEY frame (frame_id)
  --  REFERENCES lynx_frame (id)
  --  ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='LYNX Technik card modules';

CREATE TABLE lynx_controller
(
  id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique controller id',
  frame_id INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'Frame reference',
  ip_address INT(10) UNSIGNED NOT NULL,
  community VARCHAR(64) NOT NULL DEFAULT 'public',
  last_discovery DATETIME DEFAULT NULL,

  PRIMARY KEY (id),
  KEY ip_address (ip_address),
  CONSTRAINT lynx_controller_frame
    FOREIGN KEY frame (frame_id)
    REFERENCES lynx_frame (id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8
  COMMENT='LYNX Technik controller module information';

CREATE TABLE lynx_events
(
  id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique event id',
  module_id INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'Optional module reference',
  timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp this event occured',
  controller_ip INT(10) UNSIGNED NOT NULL COMMENT 'The IP we got this event from. Useful for removed modules',
  address INT(10) UNSIGNED NOT NULL COMMENT 'Internal LYNX Technik module address (e.g. 1.0.0.1)',
  event_type ENUM('problem', 'recovery', 'change') COMMENT 'The type of this event',
  message TEXT COMMENT 'A plaintext message telling us more about this event',

  PRIMARY KEY (id),
  CONSTRAINT lynx_events_module
    FOREIGN KEY module (module_id)
    REFERENCES lynx_module (id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Lynx event history';

CREATE TABLE lynx_icinga_template
(
  id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique template id',
  name VARCHAR(64) NOT NULL COMMENT 'Icinga template name',
  title VARCHAR(255) NOT NULL COMMENT 'Icinga template name',
  type enum('host','service') NOT NULL,

  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Available Icinga templates, used when generating comments';

CREATE TABLE lynx_icinga_host
(
  id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique Icinga host id',
  template_id INT(10) UNSIGNED DEFAULT NULL COMMENT 'Icinga template reference',
  host_name VARCHAR(255) NOT NULL COMMENT 'Icinga host name',

  PRIMARY KEY (id),
  UNIQUE INDEX host_name (host_name),
  CONSTRAINT lynx_icinga_host_template
    FOREIGN KEY template (template_id)
    REFERENCES lynx_icinga_template (id)
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Icinga hosts that will be generated for LYNX services';

CREATE TABLE lynx_icinga_service
(
  id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique Icinga service id',
  host_id INT(10) UNSIGNED NOT NULL COMMENT 'Icinga host reference',
  template_id INT(10) UNSIGNED DEFAULT NULL COMMENT 'Icinga template reference',
  service_description VARCHAR(255) NOT NULL COMMENT 'Icinga service description / identifier',

  PRIMARY KEY (id),
  KEY search_host (host_id),
  UNIQUE INDEX service_description (service_description),
  CONSTRAINT lynx_icinga_service_host
    FOREIGN KEY host (host_id)
    REFERENCES lynx_icinga_host (id)
    ON DELETE RESTRICT,
  CONSTRAINT lynx_icinga_service_template
    FOREIGN KEY template (template_id)
    REFERENCES lynx_icinga_template (id)
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Icinga services watching LYNX Technik modules';

CREATE TABLE lynx_icinga_service_modules
(
  service_id INT(10) UNSIGNED NOT NULL COMMENT 'Icinga service reference',
  module_id INT(10) UNSIGNED NOT NULL COMMENT 'LYNX Technik module reference',

  PRIMARY KEY  (service_id, module_id),
  CONSTRAINT lynx_service_modules_service
    FOREIGN KEY service (service_id)
    REFERENCES lynx_icinga_service (id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT lynx_service_modules_module
    FOREIGN KEY module (module_id)
    REFERENCES lynx_module (id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This assigns LYNX Technik modules to Icinga services';


