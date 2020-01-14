CREATE TABLE section (
  id int(11) NOT NULL auto_increment,
  name varchar(255) NOT NULL default '',
  user_id tinyint(3) unsigned NOT NULL default '0',
  status enum('Active','Inactive') NOT NULL default 'Active',
  PRIMARY KEY  (id),
  KEY user_id (user_id)
);

ALTER TABLE section ADD INDEX primary_display_idx (user_id, status, name);


CREATE TABLE item (
  id int(11) NOT NULL auto_increment,
  section_id int(11) NOT NULL default '0',
  task varchar(255) NOT NULL default '',
  status enum('Open','Closed','Deleted') NOT NULL default 'Open',
  created datetime NOT NULL default '0000-00-00 00:00:00',
  completed datetime NOT NULL default '0000-00-00 00:00:00',
  priority TINYINT(3) UNSIGNED NOT NULL DEFAULT '1',
  estimate decimal(5,1) NOT NULL default '0.0',
  user_id tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY user_id (user_id),
  KEY section_id (section_id),
  KEY status (status),
  KEY primary_display_idx (section_id,status,priority,task)
);

ALTER TABLE item ADD index primary_count_idx (user_id, status, completed);

CREATE TABLE user (
  id tinyint(3) unsigned NOT NULL auto_increment,
  username varchar(32) NOT NULL default '',
  fullname tinytext NOT NULL,
  password tinytext NOT NULL,
  timezone VARCHAR(32) NOT NULL DEFAULT 'US/Eastern',
  display_stylesheet_id SMALLINT(5) UNSIGNED NOT NULL,
  print_stylesheet_id SMALLINT(5) UNSIGNED NOT NULL,
  export_stylesheet_id SMALLINT(5) UNSIGNED NOT NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY username (username)
);

CREATE TABLE session (
  id int(10) unsigned NOT NULL auto_increment,
  session_id VARCHAR(255) NOT NULL,
  stamp DATETIME NOT NULL,
  contents TEXT,
  PRIMARY KEY  (id),
  UNIQUE (session_id),
  INDEX (stamp)
);


CREATE TABLE user_stylesheet (
  id SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id TINYINT(3) UNSIGNED NOT NULL,
  sheet_name VARCHAR(32) NOT NULL,
  sheet_type ENUM('display', 'print', 'export') DEFAULT 'display' NOT NULL,
  public ENUM('y', 'n') DEFAULT 'n' NOT NULL,
  contents TEXT NOT NULL,
  PRIMARY KEY (id),
  KEY (user_id)
);


CREATE TABLE recurring_item (
  id INT(11) NOT NULL AUTO_INCREMENT,
  user_id TINYINT(3) UNSIGNED NOT NULL default '0',
  task VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  KEY user_id (user_id),
  UNIQUE user_task (user_id, task)
);

