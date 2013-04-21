use msecurel_bitsecurelabs;

alter table users
add company_name varchar(200) null; 

alter table users
add phone_number varchar(20) null; 

alter table users
add address varchar(300) null; 

alter table users
add price decimal(10,2) null; 

alter table users
add comments varchar(200) null; 

/* permissions */

alter table users
add can_do_everything bit null default 0; 

alter table users
add can_create_license bit null default 0; 

alter table users
add can_edit_license bit null default 0; 

alter table users
add can_view_licenses bit null default 0; 

alter table users
add can_create_full_license bit null default 0;  

alter table users
add can_create_trial_license bit null default 0;  

alter table users
add can_only_view_his_licenses bit null default 0;

alter table users
add can_create_av_home_product bit null default 0;  

alter table users
add can_create_network_product bit null default 0; 

alter table users
add can_create_web_security_product bit null default 0; 

alter table users
add can_create_management_for_cloud_product bit null default 0; 

alter table users
add can_create_management_for_bpd_product bit null default 0; 

/* log table */
CREATE TABLE IF NOT EXISTS `log_licenses` (
  id int(10) not null auto_increment,
  admin_id int(10) not null,
  license_id int(10) not null,
  PRIMARY KEY (`id`)
) 