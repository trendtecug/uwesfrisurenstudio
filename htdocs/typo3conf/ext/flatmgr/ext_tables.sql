#
# Table structure for table 'tx_flatmgr_flat'
#
CREATE TABLE tx_flatmgr_flat (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    sorting int(10) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    name varchar(255) DEFAULT '' NOT NULL,
	flexdata text,
    capacity tinyint(4) DEFAULT '4' NOT NULL,
    capacitytext varchar(255) DEFAULT '' NOT NULL,
    marketing varchar(255) DEFAULT '' NOT NULL,
    livingspace int(11) DEFAULT '0' NOT NULL,
    landarea int(11) DEFAULT '0' NOT NULL,
    minprice varchar(255) DEFAULT '' NOT NULL,
    attribute varchar(255) DEFAULT '' NOT NULL,
    video text,

    street varchar(255) DEFAULT '' NOT NULL,
    zip varchar(255) DEFAULT '' NOT NULL,
    city varchar(255) DEFAULT '' NOT NULL,
    country varchar(255) DEFAULT '' NOT NULL,

    pic tinytext NOT NULL,
    url tinytext NOT NULL,
    description  int(11) DEFAULT '0' NOT NULL,
    pricepeakseason varchar(255) DEFAULT '' NOT NULL,
    pricelowseason varchar(255) DEFAULT '' NOT NULL,
    pricesavingseason varchar(255) DEFAULT '' NOT NULL,

    showalternativeprices tinyint(4) DEFAULT '0' NOT NULL,
    alternativepricepeakseason varchar(255) DEFAULT '' NOT NULL,
    alternativepricelowseason varchar(255) DEFAULT '' NOT NULL,
    alternativepricesavingseason varchar(255) DEFAULT '' NOT NULL,

    caution varchar(255) DEFAULT '' NOT NULL,
    special int(11) DEFAULT '0' NOT NULL,

    priceendcleaning varchar(255) DEFAULT '' NOT NULL,
    dayofarrival tinyint(4) DEFAULT '0' NOT NULL,

    gallery varchar(255) DEFAULT '' NOT NULL,

       feuseruid int(11) DEFAULT '0' NOT NULL,
       category int(11) DEFAULT '0' NOT NULL,
    tx_locator_lat varchar(255) DEFAULT '' NOT NULL,
    tx_locator_lon varchar(255) DEFAULT '' NOT NULL,
    tx_locator_geocode int(4) DEFAULT '1' NOT NULL,
    tx_locator_toplocation int(4) DEFAULT '0' NOT NULL,

    showmap tinyint(4) DEFAULT '1' NOT NULL,
    showcalendar tinyint(4) DEFAULT '1' NOT NULL,
    showfutureyearsincalendar tinyint(4) DEFAULT '1' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);

#
# Table structure for table 'tx_flatmgr_description'
#
CREATE TABLE tx_flatmgr_description (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    parenttable varchar(255) DEFAULT '' NOT NULL,
    parentuid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    sorting int(10) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    sys_language_uid int(11) DEFAULT '0' NOT NULL,
    l10n_parent int(11) DEFAULT '0' NOT NULL,
    l10n_diffsource mediumtext,
    fe_group int(11) DEFAULT '0' NOT NULL,
    name varchar(255) DEFAULT '' NOT NULL,
    description text NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);

#
# Table structure for table 'tx_flatmgr_special'
#
CREATE TABLE tx_flatmgr_special (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    parenttable varchar(255) DEFAULT '' NOT NULL,
    parentuid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    sorting int(10) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    sys_language_uid int(11) DEFAULT '0' NOT NULL,
    l10n_parent int(11) DEFAULT '0' NOT NULL,
    l10n_diffsource mediumtext,
#    fe_group int(11) DEFAULT '0' NOT NULL,
    name varchar(255) DEFAULT '' NOT NULL,
    description text NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);

#
# Table structure for table 'tx_flatmgr_season'
#
CREATE TABLE tx_flatmgr_season (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    sorting int(10) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    fe_group int(11) DEFAULT '0' NOT NULL,
    year int(11) DEFAULT '2011' NOT NULL,
    peakseason varchar(255) DEFAULT '' NOT NULL,
    lowseason varchar(255) DEFAULT '' NOT NULL,
    savingseason varchar(255) DEFAULT '' NOT NULL,

    alternativepeakseason varchar(255) DEFAULT '' NOT NULL,
    alternativelowseason varchar(255) DEFAULT '' NOT NULL,
    alternativesavingseason varchar(255) DEFAULT '' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);

#
# Table structure for table 'tx_flatmgr_attribute'
#
CREATE TABLE tx_flatmgr_attribute (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    sorting int(10) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    fe_group int(11) DEFAULT '0' NOT NULL,
    name varchar(255) DEFAULT '' NOT NULL,
    icon varchar(255) DEFAULT '' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);



#
# Table structure for table 'tx_flatmgr_room'
#
CREATE TABLE tx_flatmgr_room (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    sorting int(10) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    flatuid int(11) DEFAULT '0' NOT NULL,
    name varchar(255) DEFAULT '' NOT NULL,
    capacity tinyint(4) DEFAULT '4' NOT NULL,
#    pic tinytext NOT NULL,
#    url tinytext NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);


#
# Table structure for table 'tx_flatmgr_book'
#
CREATE TABLE tx_flatmgr_book (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    sorting int(10) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    flatid blob NOT NULL,
    roomuid int(11) DEFAULT '0' NOT NULL,
    startdate int(11) DEFAULT '0' NOT NULL,
    enddate int(11) DEFAULT '0' NOT NULL,
    onrequest tinyint(4) DEFAULT '0',
    bookedby tinytext,
    customernumber varchar(20) DEFAULT '',
    grownups tinyint(4) DEFAULT '2',
    childs tinyint(4) DEFAULT '0',
    memo varchar(255) DEFAULT '',
    category int(11) DEFAULT '0',
    agent tinytext,

    PRIMARY KEY (uid),
    KEY parent (pid)
);

#
# Table structure for table 'tx_flatmgr_flatcategory'
#
CREATE TABLE tx_flatmgr_flatcategory (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    sorting int(10) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    name varchar(20) DEFAULT '',

    PRIMARY KEY (uid),
    KEY parent (pid)
);


#
# Table structure for table 'tx_flatmgr_category'
#
CREATE TABLE tx_flatmgr_category (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    sorting int(10) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    name varchar(20) DEFAULT '',

    PRIMARY KEY (uid),
    KEY parent (pid)
);
