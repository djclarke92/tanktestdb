#-----------------------------------------------------------------------
# TankTest is released under the GNU General Public License v2 only 
# for non-profit organisations.
#
# Commercial and/or profit making organsations MUST obtain a commercial 
# license before using Nimrod.
#-----------------------------------------------------------------------

# create the database
create database if not exists tanktest;
use tanktest; 


# table of users
create TABLE IF NOT EXISTS `users` (
	`us_Username` varchar(50) NOT NULL default '',				# user name / email address
	`us_Name` varchar(100) NOT NULL default '',					# name
	`us_Password` text(256) NOT NULL default '',				# SHA-256 hash
	`us_AuthLevel` INT(10) NOT NULL default 0,					# security auth level
	`us_Features` char(10) NOT NULL default 'NNNNNNNNNN',		# optional features for each user
	`us_StationName` char(50) NOT NULL default '',				# 
	`us_StationNumber` char(20) NOT NULL default '',			# 
	`us_Address1` char(50) NOT NULL default '',					# address line 2
	`us_Address2` char(50) NOT NULL default '',					# address line 2
	`us_Address3` char(50) NOT NULL default '',					# address line 3
	`us_PostCode` varchar(10) NOT NULL default '',				#
	`us_StationEmail` char(50) NOT NULL default '',				# 
	`us_SignatoryNumber` varchar(10) NOT NULL default '',		#
	`us_Signature` blob NOT NULL default '',					# signature image
	`us_NextPeriodicCertNo` int(10) not null default 0,			# next periodic cert number ot use
	`us_PeriodicCertNoQty` int(10) not null default 0,			# how many in this range
	`us_Logo` varchar(50) NOT NULL default "",					# jpg/png file name in the images directory
	PRIMARY KEY (`us_Username`),
	KEY `us_name_index` (`us_Name`)
);

# table of customers
create TABLE IF NOT EXISTS `customers` (
	`cu_CustomerNo` int(10) unsigned NOT NULL auto_increment,   # unique record number
	`cu_Surname` varchar(50) NOT NULL default '',				#
	`cu_Firstname` varchar(50) NOT NULL default '',				#
	`cu_Email` varchar(50) NOT NULL default '',					#
	`cu_Address1` varchar(50) NOT NULL default '',				#
	`cu_Address2` varchar(50) NOT NULL default '',				#
	`cu_Address3` varchar(50) NOT NULL default '',				#
	`cu_PostCode` varchar(10) NOT NULL default '',				#
	`cu_Phone1` varchar(20) NOT NULL default '',				#
	`cu_Phone2` varchar(20) NOT NULL default '',				#
	`cu_Notes` text NOT NULL default '',						#
	PRIMARY KEY (`cu_CustomerNo`),
	KEY `cu_NameIndex` (`cu_Surname`,`cu_Firstname`)
);

# table of cylinders - the same cylinder can have multiple records if sold to another customer
create TABLE IF NOT EXISTS `cylinders` (
	`cy_CylinderNo` int(10) unsigned NOT NULL auto_increment,	# unique number
	`cy_CustomerNo` int(10) unsigned NOT NULL,					# link to customers table
	`cy_Specifications` varchar(20) NOT NULL default '',		#
	`cy_SerialNo` varchar(20) NOT NULL default '',				#
	`cy_Material` char(2) NOT NULL default '',					# link to cylindertypes table: ST, SL CF
	`cy_Manufacturer` varchar(50) NOT NULL default '',			#
	`cy_LabNo` varchar(20) NOT NULL default '',					#
	`cy_ManufactureDate` timestamp NOT NULL default '0000-00-00',	#
	PRIMARY KEY (`cy_CylinderNo`)
);

# table of cylinder types
create TABLE IF NOT EXISTS `cylindertypes` (
	`ct_CylinderType` char(2) NOT NULL,							# ST, AL, CF
	`ct_Description` varchar(20) NOT NULL default'',			# Steel, Aluminium, Carbon Fibre
	PRIMARY KEY (`ct_CylinderType`)
);

# table of cylinder checks
create TABLE IF NOT EXISTS `cylinderchecks` (					# ST: Clean Interior, ST:Slight Rust, AL: Serious Corrosion
	`cc_CylinderCheckNo` int(10) unsigned NOT NULL auto_increment,		# unique number
	`cc_CylinderType` char(2) NOT NULL,							# link to cylindertypes table
	`cc_Description` varchar(50) NOT NULL default '',			#
	PRIMARY KEY (`cc_CylinderCheckNo`),
	KEY `cc_CylinderTypeIndex` (`cc_CylinderType`)
);

# table of examinations - one examination per cylinder
create TABLE IF NOT EXISTS `examinations` (
	`ex_ExaminationNo` int(10) unsigned NOT NULL auto_increment,	#
	`ex_CustomerNo` int(10) unsigned NOT NULL,						# link to customers table
	`ex_CylinderNo` int(10) unsigned NOT NULL,						# link to cylinders table
	`ex_PaintCondition` varchar(20) NOT NULL default '',			#
	`ex_Colour` varchar(20) NOT NULL default '',					#
	`ex_MinorScratches` char(1) NOT NULL default 'N',				# Y/N
	`ex_SeriousScratches` char(1) NOT NULL default 'N',				# Y/N
	`ex_ExternalPass` char(1) NOT NULL default 'N',					# Y/N
	`ex_Notes` varchar(255) NOT NULL default '',					#
	`ex_InternalPass` char(1) NOT NULL default 'N',					# Y/N
	`ex_RingFitted` char(1) NOT NULL default 'N',					# Y/N
	`ex_RingColour` varchar(20) NOT NULL default '',				#
	`ex_TestPressure` varchar(20) NOT NULL default '',				#
	`ex_WaterCapacity` varchar(20) NOT NULL default '',				#
	`ex_MPE` varchar(20) NOT NULL default '',						#
	`ex_AccuracyVerified` char(1) NOT NULL default 'N',				# Y/N
	`ex_BuretReading` varchar(20) NOT NULL default '',				#
	`ex_HydrostaticPass` char(1) NOT NULL default 'N',				# Y/N
	`ex_RepeatVisual` char(1) NOT NULL default 'N',					# Y/N
	`ex_ExistingHydroMark` blob NOT NULL default '',				# contains symbols
	`ex_NewHydroMark` blob NOT NULL default '',						# contains symbols
	`ex_SignatoryUserName` varchar(50) NOT NULL,					# link to users table
	`ex_ExaminationDate` timestamp NOT NULL default '0000-00-00',	#
	`ex_PeriodicCertNo` int(10) NOT NULL default 0,					# cert number from NZUA booklet
	`ex_EmailedDate` timestamp NOT NULL default '0000-00-00',		# emailed database
	`ex_ReminderDate` timestamp NOT NULL default '0000-00-00',		# when did the next year reminder get emailed
	`ex_AbsenceReason` varchar(255) NOT NULL default '',			#
	`ex_ExistingHydroMarkText` varchar(50) NOT NULL default '',		#
	`ex_NewHydroMarkText` varchar(50) NOT NULL default '',			#
	`ex_Pdf` blob NOT NULL default '',								# PDF document
	PRIMARY KEY (`ex_ExaminationNo`),
	KEY `ex_CylinderNoIndex` (`ex_CylinderNo`)
);

# table of internal inspections - many per examination
create TABLE IF NOT EXISTS `inspections` (
	`in_InspectionNo` int(10) unsigned NOT NULL auto_increment,		#
	`in_ExaminationNo` int(10) unsigned NOT NULL,					# link to examinations table
	`in_CylinderCheckNo` int(10) unsigned NOT NULL,							# link to cylindercheck table
	`in_CheckPositive` char(1) NOT NULL default 'N',				# Y/N
	PRIMARY KEY (`in_InspectionNo`),
	KEY `in_ExaminationNoIndex` (`in_ExaminationNo`)
);

# table of events
# ev_DeviceNo=-1: unused
# ev_DeviceNo=-2: database structure version
# ev_DeviceNo=-3: user login attempt
create TABLE IF NOT EXISTS `events` (
    `ev_EventNo` int(10) unsigned NOT NULL auto_increment,          # unique record number
    `ev_Timestamp` timestamp NOT NULL default '0000-00-00',         # event timestamp
    `ev_DeviceNo` int(10) NOT NULL default '0',                     #
    `ev_IOChannel` int(10) NOT NULL default '0',                    #
    `ev_EventType` int(10) NOT NULL default '0',                    #
    `ev_Value` decimal(10,3) NOT NULL default '0',                  #
    `ev_Description` varchar(250) NOT NULL default '',              #
    PRIMARY KEY (`ev_EventNo`),
    KEY `ev_device_index` (`ev_DeviceNo`,`ev_Timestamp`)
) ;



# add default table records:
# 
insert into users (us_Username,us_Name,us_Password,us_AuthLevel) values ('tanktest@flatcatit.co.nz','TankTest Admin User','258497f62679c89a7ac952b27c2d2c6040cf8da412b8dd044d11156db0986b55',9);

# tanktest and tanktest_user are changed to 
GRANT insert,select,update,delete,create,drop,alter,lock tables on tanktest.* to tanktest_user@'localhost' identified by 'passw0rd.23';
GRANT insert,select,update,delete,create,drop,alter,lock tables on tanktest.* to tanktest_user@'%' identified by 'passw0rd.23';

INSERT INTO cylindertypes (cc_CylinderType,ct_Description) values('ST', 'Steel');
INSERT INTO cylindertypes (cc_CylinderType,ct_Description) values('AL', 'Aluminium');

INSERT INTO cylinderchecks (cc_CylinderType,cc_Description) VALUES( 'ST', 'Clean Interior');
INSERT INTO cylinderchecks (cc_CylinderType,cc_Description) VALUES( 'ST', 'Slight Rust');
INSERT INTO cylinderchecks (cc_CylinderType,cc_Description) VALUES( 'ST', 'Slight Corrosion');
INSERT INTO cylinderchecks (cc_CylinderType,cc_Description) VALUES( 'ST', 'Needs Rumbling');
INSERT INTO cylinderchecks (cc_CylinderType,cc_Description) VALUES( 'AL', 'Clean Interior');
INSERT INTO cylinderchecks (cc_CylinderType,cc_Description) VALUES( 'AL', 'Some oxide deposits');
INSERT INTO cylinderchecks (cc_CylinderType,cc_Description) VALUES( 'AL', 'Serious Corrosion');
INSERT INTO cylinderchecks (cc_CylinderType,cc_Description) VALUES( 'AL', 'Neck/shoulder cracks');




