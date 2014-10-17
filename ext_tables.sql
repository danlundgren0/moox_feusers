#
# Extend table structure of table 'fe_users'
#
CREATE TABLE fe_users (	
	falImages int(10) unsigned NOT NULL DEFAULT '0',
	is_company_admin tinyint(3) DEFAULT '0' NOT NULL,
    is_moox_feuser tinyint(3) DEFAULT '0' NOT NULL
);