#
# Table structure for table 'tx_x4econgress_congresses_categories_mm'
# 
#
CREATE TABLE tx_x4econgress_congresses_categories_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);




#
# Table structure for table 'tx_x4econgress_congresses_persons_mm'
# 
#
CREATE TABLE tx_x4econgress_congresses_persons_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);




#
# Table structure for table 'tx_x4econgress_congresses_feusers_mm'
# 
#
CREATE TABLE tx_x4econgress_congresses_feusers_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);



#
# Table structure for table 'tx_x4econgress_congresses'
#
CREATE TABLE tx_x4econgress_congresses (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    sys_language_uid int(11) DEFAULT '0' NOT NULL,
    l10n_parent int(11) DEFAULT '0' NOT NULL,
    l10n_diffsource mediumtext,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    name tinytext,
    rparent int(11) DEFAULT '0' NOT NULL,
    description text,
    date_from int(11) DEFAULT '0' NOT NULL,
    date_to int(11) DEFAULT '0' NOT NULL,
    categories int(11) DEFAULT '0' NOT NULL,
    fe_user int(11) DEFAULT '0' NOT NULL,
    persons int(11) DEFAULT '0' NOT NULL,
    files text,
    max_participants int(11) DEFAULT '0' NOT NULL,
    payment_info text,
    speaker_info text,
    registration_deadline int(11) DEFAULT '0' NOT NULL,
    course_reg_deadline text,
    notification_email tinytext,
    format text,
    form text,
    clang text,
    audience text,
    teacher text,
    administration text,
    contact text,
    schedule text,
    location text,
    host text,
    website text,
    intranet text,
    diverse text,
    publish tinyint DEFAULT '0' NOT NULL,
    
    PRIMARY KEY (uid),
    KEY parent (pid)
);


#
# Table structure for table 'tx_x4econgress_participants'
#
CREATE TABLE tx_x4econgress_participants (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	type int(11) DEFAULT '0' NOT NULL,
	congress_id int(11) DEFAULT '0' NOT NULL,
	feuser_id int(11) DEFAULT '0' NOT NULL,
	name tinytext NOT NULL,
	firstname tinytext NOT NULL,
	address text NOT NULL,
	zip tinytext NOT NULL,
	city tinytext NOT NULL,
	country tinytext NOT NULL,
	email tinytext NOT NULL,
	phone tinytext NOT NULL,
	worklocation tinytext NOT NULL,
	remarks text NOT NULL,
	poster_title text NOT NULL,
	poster_abstract text NOT NULL,
	poster_detail text NOT NULL,
	poster_images blob NOT NULL,
	uploads blob NOT NULL,
	dl_files text,
	discussant text NOT NULL,
	random_key tinytext NOT NULL,
	veggie tinyint DEFAULT '0' NOT NULL,
	payed tinyint DEFAULT '0' NOT NULL,
	evening tinyint DEFAULT '0' NOT NULL,
	custom text NOT NULL,
	gender varchar(1) DEFAULT '' NOT NULL,
	birthyear int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);

#
# Table structure for table 'tx_x4econgress_categories'
#
CREATE TABLE tx_x4econgress_categories (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    sys_language_uid int(11) DEFAULT '0' NOT NULL,
    l10n_parent int(11) DEFAULT '0' NOT NULL,
    l10n_diffsource mediumtext,
    sorting int(10) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    title tinytext,
    
    PRIMARY KEY (uid),
    KEY parent (pid)
);