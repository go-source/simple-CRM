CREATE TABLE IF NOT EXISTS `#__gscrm_codes` (
  `gscrm_code_id` int(11) unsigned NOT NULL auto_increment,
  `user_id` int(11) NOT NULL COMMENT 'joomla user code owner',  
  `title` varchar(144) NOT NULL COMMENT 'company codename',
  `type` tinyint(1) DEFAULT '0' COMMENT '0=free 1=paid',
  `menu` tinyint(1) DEFAULT '0' COMMENT 'show menu in comp',
  `timezone` varchar(40) NOT NULL DEFAULT 'UTC',
  `date_format` varchar(40) NOT NULL DEFAULT 'Y-m-d',
  `hour_format` varchar(40) NOT NULL DEFAULT 'H:i:s',      
  `pref_addr` tinyint(1) DEFAULT '0' COMMENT '1=street-number', 
  `hide_note` tinyint(1) DEFAULT '0' COMMENT '1=hide notes selector',
  `member_count` varchar(144) NOT NULL DEFAULT '1' COMMENT 'updated at congif',
  `member_max` varchar(144) NOT NULL DEFAULT '5' NOT NULL COMMENT 'max count paid',
  `valid_tru` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',           
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `access` int(10) NOT NULL DEFAULT '2',  
  PRIMARY KEY (`gscrm_code_id`)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__gscrm_beads` (
  `gscrm_bead_id` int(11) unsigned NOT NULL auto_increment,
  `user_name` varchar(144) NOT NULL COMMENT 'user name from Joomla',  
  `user_id` int(11) NOT NULL COMMENT 'joomla user = manager', 
  `quota` float( 11, 2 ) NOT NULL DEFAULT '0' COMMENT 'quota',
  `sdate` date NOT NULL DEFAULT '0000-00-00' COMMENT 'quota start',
  `edate` date NOT NULL DEFAULT '0000-00-00' COMMENT 'quota deadline',    
  `currency` int(11) NOT NULL NOT NULL DEFAULT '0' COMMENT 'currency from list',    
  `role` int(11) NOT NULL DEFAULT '0'COMMENT 'user role',   
  `code` int(11) NOT NULL COMMENT 'company code',     
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0', 
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `access` int(10) NOT NULL DEFAULT '2',  
  PRIMARY KEY (`gscrm_bead_id`)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;  

CREATE TABLE IF NOT EXISTS `#__gscrm_roles` (
  `gscrm_role_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(144) NOT NULL COMMENT 'role name',
  `parent` int(11) NOT NULL DEFAULT '0' COMMENT 'parent id',   
  `child` int(11) NOT NULL DEFAULT '0' COMMENT 'child id',
  `code` int(11) NOT NULL COMMENT 'company code',       
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0', 
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `access` int(10) NOT NULL DEFAULT '2',  
  PRIMARY KEY (`gscrm_role_id`)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8; 

CREATE TABLE IF NOT EXISTS `#__gscrm_notes` (
  `gscrm_note_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(52) NOT NULL COMMENT 'short title for search',
  `content` text COMMENT 'long description',
  `type` int(11) NOT NULL COMMENT 'drop list', 
  `account` int(11) NOT NULL,   
  `opportunity` int(11) NOT NULL DEFAULT '0',     
  `contract` int(11) NOT NULL DEFAULT '0',
  `order_id` int(11) NOT NULL DEFAULT '0',  
  `invoice` int(11) NOT NULL DEFAULT '0',      
  `owner` int(11) NOT NULL DEFAULT '0' COMMENT 'if re-assigned',
  `code` int(11) NOT NULL COMMENT 'group control access',   
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `access` int(10) NOT NULL DEFAULT '2',  
  PRIMARY KEY (`gscrm_note_id`)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__gscrm_accounts` (
  `gscrm_account_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(56) NOT NULL COMMENT 'full name',
  `unique_id` varchar(20) COMMENT 'unique national identifier',  
  `company` int(11) NOT NULL DEFAULT '0',
  `notes` text COMMENT 'account notes',
  `type` tinyint(1) NOT NULL DEFAULT '2' COMMENT '1=business 2=person',
  `has_relation` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'n:n to accounts',
  `gender` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=NA 1=male 2=female',       
  `birthdate` date NOT NULL DEFAULT '0000-00-00',
  `territory` int(11) NOT NULL DEFAULT '0',
  `campaign` int(11) NOT NULL DEFAULT '0',  
  `email` varchar(52) COMMENT 'main email id',
  `address` int(11) NOT NULL DEFAULT '0' COMMENT 'main address id',
  `phone1` varchar(20),
  `phone2` varchar(20),
  `phone3` varchar(20),
  `phone4` varchar(20),  
  `code` int(11) NOT NULL COMMENT 'group control access',  
  `owner` int(11) NOT NULL DEFAULT '0' COMMENT 'owner of account',
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',  
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `access` int(10) NOT NULL DEFAULT '2',  
  PRIMARY KEY (`gscrm_account_id`)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__gscrm_relations` (
  `gscrm_relation_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(144) NOT NULL COMMENT 'name of relation',
  `code` int(11) NOT NULL,    
  `parent` int(11) NOT NULL,  
  `child` int(11) NOT NULL,
  PRIMARY KEY (`gscrm_relation_id`)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__gscrm_addresses` (
  `gscrm_address_id` int(11) unsigned NOT NULL auto_increment,
  `main` tinyint(1) DEFAULT '1' COMMENT '1=main address',    
  `street` varchar(50),
  `number` varchar(10),  
  `additional` text,
  `city` varchar(50),
  `state` varchar(50),
  `country` varchar(50), 
  `zip` varchar(50), 
  `lat` float( 10, 6 ) NOT NULL DEFAULT '0' COMMENT 'Goolge Latitude',
  `lng` float( 10, 6 ) NOT NULL DEFAULT '0' COMMENT 'Goolge Longitude',     
  `account` int(11) NOT NULL,  
  `code` int(11) NOT NULL COMMENT 'group control access',  
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',  
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `access` int(10) NOT NULL DEFAULT '2',  
  PRIMARY KEY (`gscrm_address_id`)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__gscrm_emails` (
  `gscrm_email_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(144) NOT NULL COMMENT 'email address',
  `main` tinyint(1) DEFAULT '1' COMMENT '1=preferred email',
  `account` int(11) NOT NULL COMMENT 'account id',  
  `code` int(11) NOT NULL COMMENT 'group control access',  
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',  
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `access` int(10) NOT NULL DEFAULT '2',  
  PRIMARY KEY (`gscrm_email_id`)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__gscrm_territories` (
  `gscrm_territory_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(144) NOT NULL, 
  `code` int(11) NOT NULL COMMENT 'group control access',  
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',  
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `access` int(10) NOT NULL DEFAULT '2',  
  PRIMARY KEY (`gscrm_territory_id`)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__gscrm_campaigns` (
  `gscrm_campaign_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(144) NOT NULL,
  `target` float( 11, 2 ) NOT NULL DEFAULT '0' COMMENT 'target value',   
  `currency` int(11) NOT NULL NOT NULL DEFAULT '0' COMMENT 'currency from list',
  `code` int(11) NOT NULL COMMENT 'group control access',  
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',  
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `access` int(10) NOT NULL DEFAULT '2',  
  PRIMARY KEY (`gscrm_campaign_id`)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
  
CREATE TABLE IF NOT EXISTS `#__gscrm_currencies` (
  `gscrm_currency_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(50) NOT NULL COMMENT 'currency name',
  `symbol` varchar(4) NOT NULL COMMENT 'currency symbol',
  `rate` float( 7, 3 ) NOT NULL DEFAULT '1' COMMENT 'conversion rate',
  `code` int(11) NOT NULL COMMENT 'group control access',     
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',  
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `access` int(10) NOT NULL DEFAULT '2',   
  PRIMARY KEY (`gscrm_currency_id`)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;   
  
CREATE TABLE IF NOT EXISTS `#__gscrm_orders` (
  `gscrm_order_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(144) NOT NULL COMMENT 'order title',
  `number` int(11) NOT NULL COMMENT 'order number', 
  `type` tinyint(1) NOT NULL DEFAULT '2' COMMENT '1=business 2=person',   
  `notes` text COMMENT 'order initial note',
  `in_out` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0=return 1=delivery',
  `service` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'droplist 0=backlog to 4=reopen',   
  `value` float( 11, 2 ) NOT NULL DEFAULT '0' COMMENT 'delivery value',
  `currency` int(11) NOT NULL NOT NULL DEFAULT '0' COMMENT 'currency from list',      
  `owner` int(11) NOT NULL DEFAULT '0' COMMENT 'order owner',  
  `contract` int(11) NOT NULL DEFAULT '0' COMMENT '0= no contract',
  `opportunity` int(11) NOT NULL DEFAULT '0' COMMENT '0= direct',   
  `account` int(11) NOT NULL,
  `company` int(11) NOT NULL DEFAULT '0',
  `note` int(11) NOT NULL DEFAULT '0' COMMENT 'contact that originated order', 
  `params` text COLLATE utf8mb4_unicode_ci COMMENT '1st creation',      
  `code` int(11) NOT NULL COMMENT 'group control access',   
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',  
  `enabled` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0=date expired',
  `access` int(10) NOT NULL DEFAULT '2',  
  PRIMARY KEY (`gscrm_order_id`)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;    
  
 CREATE TABLE IF NOT EXISTS `#__gscrm_opportunities` (
  `gscrm_opportunity_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(144) NOT NULL, 
  `notes` text COMMENT 'opp initial note',  
  `stage` tinyint(2) NOT NULL DEFAULT '0' COMMENT 'interest level',  
  `value` float( 11, 2 ) NOT NULL DEFAULT '0' COMMENT 'opportunity value',
  `currency` int(11) NOT NULL NOT NULL DEFAULT '0' COMMENT 'currency from list',  
  `owner` int(11) NOT NULL DEFAULT '0' COMMENT 'opportunity owner', 
  `account` int(11) NOT NULL,
  `company` int(11) NOT NULL DEFAULT '0',
  `note` int(11) NOT NULL DEFAULT '0' COMMENT 'note from origin',     
  `code` int(11) NOT NULL COMMENT 'group control access',  
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',  
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `access` int(10) NOT NULL DEFAULT '2',  
  PRIMARY KEY (`gscrm_opportunity_id`)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8; 
  
