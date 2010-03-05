
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

#-----------------------------------------------------------------------------
#-- cdr
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `cdr`;


CREATE TABLE `cdr`
(
	`calldate` DATETIME  NOT NULL,
	`clid` VARCHAR(80)  NOT NULL,
	`src` VARCHAR(80)  NOT NULL,
	`dst` VARCHAR(80)  NOT NULL,
	`dcontext` VARCHAR(80)  NOT NULL,
	`channel` VARCHAR(80),
	`dstchannel` VARCHAR(80)  NOT NULL,
	`lastapp` VARCHAR(80)  NOT NULL,
	`lastdata` VARCHAR(80)  NOT NULL,
	`duration` INTEGER(11)  NOT NULL,
	`billsec` INTEGER(11)  NOT NULL,
	`disposition` VARCHAR(45)  NOT NULL,
	`amaflags` INTEGER(11)  NOT NULL,
	`accountcode` VARCHAR(30),
	`uniqueid` VARCHAR(32) default '' NOT NULL,
	`userfield` VARCHAR(255)  NOT NULL,
	`destination_type` INTEGER(1) default 0 NOT NULL,
	`ar_asterisk_account_id` INTEGER,
	`income_ar_rate_id` INTEGER(20) default null,
	`income` INTEGER(20) default null,
	`cost_ar_rate_id` INTEGER(20) default null,
	`vendor_id` INTEGER(20) default null,
	`cost` INTEGER(20) default null,
	`ar_telephone_prefix_id` INTEGER,
	`cached_internal_telephone_number` VARCHAR(256),
	`cached_external_telephone_number` VARCHAR(256),
	`external_telephone_number_with_applied_portability` VARCHAR(256),
	`cached_masked_external_telephone_number` VARCHAR(256),
	`source_id` INTEGER(11) default null,
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	PRIMARY KEY (`id`),
	KEY `cdr_calldate_index`(`calldate`),
	KEY `cdr_channel_index`(`channel`),
	KEY `cdr_uniqueid_index`(`uniqueid`),
	KEY `cdr_destination_type_index`(`destination_type`),
	KEY `cdr_income_ar_rate_id_index`(`income_ar_rate_id`),
	KEY `cdr_income_index`(`income`),
	KEY `cdr_cost_ar_rate_id_index`(`cost_ar_rate_id`),
	KEY `cdr_vendor_id_index`(`vendor_id`),
	KEY `cdr_cost_index`(`cost`),
	KEY `cdr_cached_internal_telephone_number_index`(`cached_internal_telephone_number`),
	KEY `cdr_cached_external_telephone_number_index`(`cached_external_telephone_number`),
	KEY `cdr_external_telephone_number_with_applied_portability_index`(`external_telephone_number_with_applied_portability`),
	KEY `cdr_cached_masked_external_telephone_number_index`(`cached_masked_external_telephone_number`),
	KEY `cdr_source_id_index`(`source_id`),
	INDEX `cdr_FI_1` (`ar_asterisk_account_id`),
	CONSTRAINT `cdr_FK_1`
		FOREIGN KEY (`ar_asterisk_account_id`)
		REFERENCES `ar_asterisk_account` (`id`),
	INDEX `cdr_FI_2` (`ar_telephone_prefix_id`),
	CONSTRAINT `cdr_FK_2`
		FOREIGN KEY (`ar_telephone_prefix_id`)
		REFERENCES `ar_telephone_prefix` (`id`),
	CONSTRAINT `cdr_to_income_rate_key`
		FOREIGN KEY (`income_ar_rate_id`)
		REFERENCES `ar_rate` (`id`),
	CONSTRAINT `cdr_to_cost_rate_key`
		FOREIGN KEY (`cost_ar_rate_id`)
		REFERENCES `ar_rate` (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- ar_number_portability
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `ar_number_portability`;


CREATE TABLE `ar_number_portability`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`telephone_number` VARCHAR(256)  NOT NULL,
	`ported_telephone_number` VARCHAR(256)  NOT NULL,
	`from_date` DATETIME,
	PRIMARY KEY (`id`),
	KEY `ar_number_portability_telephone_number_index`(`telephone_number`),
	KEY `ar_number_portability_ported_telephone_number_index`(`ported_telephone_number`),
	KEY `ar_number_portability_from_date_index`(`from_date`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- ar_asterisk_account
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `ar_asterisk_account`;


CREATE TABLE `ar_asterisk_account`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(160),
	`account_code` VARCHAR(30)  NOT NULL,
	`ar_office_id` INTEGER,
	PRIMARY KEY (`id`),
	KEY `ar_asterisk_account_account_code_index`(`account_code`),
	INDEX `ar_asterisk_account_FI_1` (`ar_office_id`),
	CONSTRAINT `ar_asterisk_account_FK_1`
		FOREIGN KEY (`ar_office_id`)
		REFERENCES `ar_office` (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- ar_office
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `ar_office`;


CREATE TABLE `ar_office`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(128),
	`description` VARCHAR(1024),
	`ar_party_id` INTEGER,
	PRIMARY KEY (`id`),
	INDEX `ar_office_FI_1` (`ar_party_id`),
	CONSTRAINT `ar_office_FK_1`
		FOREIGN KEY (`ar_party_id`)
		REFERENCES `ar_party` (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- ar_party
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `ar_party`;


CREATE TABLE `ar_party`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`customer_or_vendor` CHAR(1) default 'C' NOT NULL,
	`name` VARCHAR(255),
	`external_crm_code` VARCHAR(40),
	`vat` VARCHAR(40),
	`legal_address` VARCHAR(60),
	`legal_city` VARCHAR(60),
	`legal_zipcode` VARCHAR(20),
	`legal_state_province` VARCHAR(60),
	`legal_country` VARCHAR(60),
	`email` VARCHAR(60),
	`phone` VARCHAR(60),
	`phone2` VARCHAR(60),
	`fax` VARCHAR(60),
	`ar_rate_category_id` INTEGER,
	`ar_params_id` INTEGER,
	`max_limit_30` INTEGER(20) default null,
	`last_email_advise_for_max_limit_30` DATETIME,
	`is_active` INTEGER default 1 NOT NULL,
	PRIMARY KEY (`id`),
	KEY `ar_party_customer_or_vendor_index`(`customer_or_vendor`),
	KEY `ar_party_max_limit_30_index`(`max_limit_30`),
	KEY `ar_party_is_active_index`(`is_active`),
	INDEX `ar_party_FI_1` (`ar_rate_category_id`),
	CONSTRAINT `ar_party_FK_1`
		FOREIGN KEY (`ar_rate_category_id`)
		REFERENCES `ar_rate_category` (`id`),
	INDEX `ar_party_FI_2` (`ar_params_id`),
	CONSTRAINT `ar_party_FK_2`
		FOREIGN KEY (`ar_params_id`)
		REFERENCES `ar_params` (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- ar_params
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `ar_params`;


CREATE TABLE `ar_params`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(80),
	`is_default` INTEGER,
	`service_name` VARCHAR(120),
	`service_provider_website` VARCHAR(120),
	`service_provider_email` VARCHAR(120),
	`vat_tax_perc` INTEGER(3) default 0 NOT NULL,
	`logo_image` VARCHAR(120),
	`slogan` VARCHAR(255),
	`footer` VARCHAR(255),
	`user_message` VARCHAR(2048),
	`last_viewed_feeds_md5` VARCHAR(1024),
	`current_feeds_md5` VARCHAR(1024),
	`legal_name` VARCHAR(255),
	`external_crm_code` VARCHAR(40),
	`vat` VARCHAR(40),
	`legal_address` VARCHAR(120),
	`legal_website` VARCHAR(120),
	`legal_city` VARCHAR(60),
	`legal_zipcode` VARCHAR(20),
	`legal_state_province` VARCHAR(60),
	`legal_country` VARCHAR(60),
	`legal_email` VARCHAR(60),
	`legal_phone` VARCHAR(60),
	`phone2` VARCHAR(60),
	`legal_fax` VARCHAR(60),
	`sender_name_on_invoicing_emails` VARCHAR(120),
	`invoicing_email_address` VARCHAR(120),
	`accountant_email_address` VARCHAR(120),
	`smtp_host` VARCHAR(250),
	`smtp_port` INTEGER(4),
	`smtp_username` VARCHAR(60),
	`smtp_password` VARCHAR(60),
	`smtp_encryption` VARCHAR(60),
	`smtp_reconnect_after_nr_of_messages` INTEGER(4),
	`smtp_seconds_of_pause_after_reconnection` INTEGER(2),
	PRIMARY KEY (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- ar_web_account
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `ar_web_account`;


CREATE TABLE `ar_web_account`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`login` VARCHAR(20)  NOT NULL,
	`password` VARCHAR(40),
	`ar_party_id` INTEGER,
	`ar_office_id` INTEGER,
	`activate_at` DATE,
	`deactivate_at` DATE,
	`ar_params_id` INTEGER,
	PRIMARY KEY (`id`),
	KEY `ar_web_account_login_index`(`login`),
	INDEX `ar_web_account_FI_1` (`ar_party_id`),
	CONSTRAINT `ar_web_account_FK_1`
		FOREIGN KEY (`ar_party_id`)
		REFERENCES `ar_party` (`id`),
	INDEX `ar_web_account_FI_2` (`ar_office_id`),
	CONSTRAINT `ar_web_account_FK_2`
		FOREIGN KEY (`ar_office_id`)
		REFERENCES `ar_office` (`id`),
	INDEX `ar_web_account_FI_3` (`ar_params_id`),
	CONSTRAINT `ar_web_account_FK_3`
		FOREIGN KEY (`ar_params_id`)
		REFERENCES `ar_params` (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- ar_invoice
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `ar_invoice`;


CREATE TABLE `ar_invoice`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`ar_party_id` INTEGER,
	`type` CHAR(1) default 'C' NOT NULL,
	`is_revenue_sharing` INTEGER default 0 NOT NULL,
	`nr` VARCHAR(20)  NOT NULL,
	`invoice_date` DATE,
	`ar_cdr_from` DATE,
	`ar_cdr_to` DATE,
	`total_without_tax` INTEGER(20) default null,
	`vat_perc` INTEGER(20) default null,
	`total_vat` INTEGER(20) default null,
	`total` INTEGER(20) default null,
	`html_details` TEXT,
	`pdf_invoice` LONGBLOB,
	`email_subject` VARCHAR(1024),
	`email_message` TEXT,
	`already_sent` INTEGER,
	PRIMARY KEY (`id`),
	KEY `ar_invoice_type_index`(`type`),
	KEY `ar_invoice_is_revenue_sharing_index`(`is_revenue_sharing`),
	KEY `ar_invoice_nr_index`(`nr`),
	KEY `ar_invoice_invoice_date_index`(`invoice_date`),
	KEY `ar_invoice_total_without_tax_index`(`total_without_tax`),
	KEY `ar_invoice_vat_perc_index`(`vat_perc`),
	KEY `ar_invoice_total_vat_index`(`total_vat`),
	KEY `ar_invoice_total_index`(`total`),
	INDEX `ar_invoice_FI_1` (`ar_party_id`),
	CONSTRAINT `ar_invoice_FK_1`
		FOREIGN KEY (`ar_party_id`)
		REFERENCES `ar_party` (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- ar_invoice_creation
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `ar_invoice_creation`;


CREATE TABLE `ar_invoice_creation`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`type` CHAR(1) default 'C' NOT NULL,
	`is_revenue_sharing` INTEGER default 0 NOT NULL,
	`first_nr` VARCHAR(20),
	`invoice_date` DATE,
	`ar_cdr_from` DATE,
	`ar_cdr_to` DATE,
	PRIMARY KEY (`id`),
	KEY `ar_invoice_creation_type_index`(`type`),
	KEY `ar_invoice_creation_is_revenue_sharing_index`(`is_revenue_sharing`),
	KEY `ar_invoice_creation_invoice_date_index`(`invoice_date`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- ar_rate_category
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `ar_rate_category`;


CREATE TABLE `ar_rate_category`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(128),
	PRIMARY KEY (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- ar_rate
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `ar_rate`;


CREATE TABLE `ar_rate`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`destination_type` INTEGER(1) default 0 NOT NULL,
	`is_exception` INTEGER default 0 NOT NULL,
	`ar_rate_category_id` INTEGER,
	`ar_party_id` INTEGER,
	`start_time` DATETIME  NOT NULL,
	`end_time` DATETIME,
	`php_class_serialization` LONGTEXT,
	`user_input` LONGTEXT,
	`note` TEXT,
	PRIMARY KEY (`id`),
	KEY `ar_rate_destination_type_index`(`destination_type`),
	KEY `ar_rate_start_time_index`(`start_time`),
	INDEX `ar_rate_FI_1` (`ar_rate_category_id`),
	CONSTRAINT `ar_rate_FK_1`
		FOREIGN KEY (`ar_rate_category_id`)
		REFERENCES `ar_rate_category` (`id`),
	INDEX `ar_rate_FI_2` (`ar_party_id`),
	CONSTRAINT `ar_rate_FK_2`
		FOREIGN KEY (`ar_party_id`)
		REFERENCES `ar_party` (`id`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- ar_telephone_prefix
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `ar_telephone_prefix`;


CREATE TABLE `ar_telephone_prefix`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`prefix` VARCHAR(40)  NOT NULL,
	`name` VARCHAR(80),
	`geographic_location` VARCHAR(80),
	`operator_type` VARCHAR(80),
	PRIMARY KEY (`id`),
	UNIQUE KEY `ar_telephone_prefix_prefix_unique` (`prefix`),
	KEY `ar_telephone_prefix_operator_type_index`(`operator_type`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- ar_problem
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `ar_problem`;


CREATE TABLE `ar_problem`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`created_at` DATETIME,
	`duplication_key` VARCHAR(160)  NOT NULL,
	`description` TEXT,
	`effect` TEXT,
	`proposed_solution` TEXT,
	`user_notes` TEXT,
	`mantain` INTEGER,
	`signaled_to_admin` INTEGER default 0 NOT NULL,
	PRIMARY KEY (`id`),
	KEY `ar_problem_duplication_key_index`(`duplication_key`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- ar_job_queue
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `ar_job_queue`;


CREATE TABLE `ar_job_queue`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`is_part_of` INTEGER(11)  NOT NULL,
	`state` INTEGER(1) default 0 NOT NULL,
	`created_at` DATETIME,
	`start_at` DATETIME,
	`end_at` DATETIME,
	`description` VARCHAR(12000)  NOT NULL,
	`php_data_job_serialization` LONGTEXT,
	PRIMARY KEY (`id`),
	KEY `ar_job_queue_is_part_of_index`(`is_part_of`),
	KEY `ar_job_queue_state_index`(`state`)
)Type=InnoDB;

#-----------------------------------------------------------------------------
#-- ar_custom_rate_form
#-----------------------------------------------------------------------------

DROP TABLE IF EXISTS `ar_custom_rate_form`;


CREATE TABLE `ar_custom_rate_form`
(
	`id` INTEGER(20)  NOT NULL,
	PRIMARY KEY (`id`),
	KEY `ar_custom_rate_form_id_index`(`id`),
	CONSTRAINT `ar_custom_rate_form_FK_1`
		FOREIGN KEY (`id`)
		REFERENCES `ar_rate` (`id`)
		ON DELETE CASCADE
)Type=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
