DROP TABLE IF EXISTS section;
CREATE TABLE section (
  id int NOT NULL auto_increment,
  name varchar(255) NOT NULL default '',
  user_id tinyint unsigned NOT NULL default '0',
  status enum('Active','Inactive') NOT NULL default 'Active',
  PRIMARY KEY (id),
  KEY user_id (user_id),
  KEY primary_display_idx (user_id,status,name)
);

DROP TABLE IF EXISTS item;
CREATE TABLE item (
  id int NOT NULL auto_increment,
  section_id int NOT NULL default '0',
  task varchar(255) NOT NULL default '',
  status enum('Open','Closed','Deleted') NOT NULL default 'Open',
  created datetime default NULL,
  completed datetime default NULL,
  priority tinyint unsigned NOT NULL default '1',
  user_id tinyint unsigned NOT NULL default '0',
  PRIMARY KEY (id),
  KEY user_id (user_id),
  KEY section_id (section_id),
  KEY status (status),
  KEY primary_display_idx (section_id,status,priority,task),
  KEY primary_count_idx (user_id,status,completed)
);

DROP TABLE IF EXISTS user;
CREATE TABLE user (
  id tinyint unsigned NOT NULL auto_increment,
  username varchar(32) NOT NULL default '',
  fullname tinytext NOT NULL,
  password tinytext NOT NULL,
  timezone varchar(128) NOT NULL default 'America/New_York',
  PRIMARY KEY (id),
  UNIQUE KEY username (username)
);

DROP TABLE IF EXISTS session;
CREATE TABLE session (
  id int unsigned NOT NULL auto_increment,
  session_id varchar(255) NOT NULL,
  stamp datetime NOT NULL,
  ip varchar(255) default NULL,
  contents text,
  PRIMARY KEY (id),
  UNIQUE KEY session_id (session_id),
  KEY stamp (stamp)
);
