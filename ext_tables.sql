#######################
## tt_content_filter ##
#######################

CREATE TABLE tt_content_filter (
	sorting int(10) DEFAULT '0' NOT NULL,
	content_uid int(10) unsigned DEFAULT '0' NOT NULL,
	plugin varchar(100) DEFAULT '' NOT NULL,
	property varchar(100) DEFAULT '' NOT NULL,
	title varchar(255) DEFAULT '' NOT NULL,
	slug varchar(255) DEFAULT '' NOT NULL,
	settings text NOT NULL,

	KEY content_uid (content_uid),
);


CREATE TABLE sys_param (
    uid INT(11) AUTO_INCREMENT PRIMARY KEY,
    pid INT(11) DEFAULT 0 NOT NULL,
    hidden TINYINT(1) DEFAULT 0 NOT NULL,
    deleted TINYINT(1) DEFAULT 0 NOT NULL,
    sys_language_uid INT(11) NOT NULL DEFAULT 0,
    l10n_parent INT(11) DEFAULT 0,
    l10n_diffsource MEDIUMBLOB DEFAULT NULL,
    sorting INT(11) NOT NULL DEFAULT '0',
    tstamp INT(11) DEFAULT 0 NOT NULL,
    crdate INT(11) DEFAULT 0 NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    type ENUM('text', 'number', 'date', 'boolean') NOT NULL DEFAULT 'text',
    input_mode ENUM('rte', 'input', 'integer', 'decimal') DEFAULT NULL,
    default_value TEXT DEFAULT NULL
) ENGINE=InnoDB;

CREATE TABLE sys_param_value (
    uid INT(11) AUTO_INCREMENT PRIMARY KEY,
    pid INT(11) DEFAULT 0 NOT NULL,
    hidden TINYINT(1) DEFAULT 0 NOT NULL,
    deleted TINYINT(1) DEFAULT 0 NOT NULL,
    sys_language_uid INT(11) NOT NULL DEFAULT 0,
    l10n_parent INT(11) DEFAULT 0,
    l10n_diffsource MEDIUMBLOB DEFAULT NULL,
    sorting INT(11) NOT NULL DEFAULT '0',
    tstamp INT(11) DEFAULT 0 NOT NULL,
    crdate INT(11) DEFAULT 0 NOT NULL,
    param INT(11) NOT NULL,
    foreign_table VARCHAR(255) NOT NULL DEFAULT '',
    foreign_object INT(11) NOT NULL,
    value_input VARCHAR(255) DEFAULT NULL,
    value_textarea TEXT DEFAULT NULL,
    value_rte TEXT DEFAULT NULL,
    value_integer INT(11) DEFAULT NULL,
    value_decimal DECIMAL(10,2) DEFAULT NULL,
    value_boolean TINYINT(1) DEFAULT NULL,
    FOREIGN KEY (param) REFERENCES sys_param(uid) ON DELETE CASCADE
) ENGINE=InnoDB;

