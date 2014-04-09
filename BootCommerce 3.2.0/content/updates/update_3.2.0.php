<?php
  $is_included = get_included_files();
  if( $is_included[0] == (__FILE__) ) die('You have no permission for direct access to this file');
  require_once(dirname(dirname(dirname(__FILE__))).'/include/inc_load.php');  
  add_column_if_not_exist($table_prefix.'products','file_name','VARCHAR(1) NOT NULL');
  add_column_if_not_exist($table_prefix.'products','active','TINYINT(1) NOT NULL DEFAULT 1');
  add_column_if_not_exist($table_prefix.'products','showcase','TINYINT(1) NOT NULL DEFAULT 0');
  execute('ALTER TABLE '.$table_prefix.'products 
     CHANGE tax tax DECIMAL(65,10) NOT NULL,
	 CHANGE price price DECIMAL(65,10) NOT NULL,
	 CHANGE offer offer DECIMAL(65,10) NOT NULL,
	 CHANGE availability availability DECIMAL(65,10) NOT NULL');
  execute('ALTER TABLE '.$table_prefix.'orders 
     CHANGE subtotal subtotal DECIMAL(65,10) NOT NULL,
	 CHANGE grandtotal grandtotal DECIMAL(65,10) NOT NULL,
	 CHANGE tax tax DECIMAL(65,10) NOT NULL,
	 CHANGE shipping_price shipping_price DECIMAL(65,10) NOT NULL,
	 CHANGE payment_price payment_price DECIMAL(65,10) NOT NULL');	 
				mysql_query("CREATE TABLE IF NOT EXISTS ".$table_prefix."plugins (
				  id bigint(20) NOT NULL AUTO_INCREMENT,
				  name varchar(250) CHARACTER SET utf8 NOT NULL,
				  shortname varchar(250) CHARACTER SET utf8 NOT NULL,
				  description longtext CHARACTER SET utf8 NOT NULL,
				  version varchar(250) CHARACTER SET utf8 NOT NULL,
				  dependence longtext CHARACTER SET utf8 NOT NULL,
				  active tinyint(1) NOT NULL DEFAULT 1,
				  min_bc_version_required varchar(10) CHARACTER SET utf8 NOT NULL,	
				  system tinyint(1) NOT NULL DEFAULT 0,			  
				  PRIMARY KEY (id)
				) DEFAULT CHARSET=utf8");	  
				mysql_query("CREATE TABLE IF NOT EXISTS ".$table_prefix."version (			
				  version varchar(250) CHARACTER SET utf8 NOT NULL
				) DEFAULT CHARSET=utf8") ;	  
 $result = execute('select * from '.$table_prefix.'version');
 $rs = mysql_fetch_array($result);
 if($rs){
   execute("update ".$table_prefix."version set version = '3.2.0'");
 }else{
   execute("insert into ".$table_prefix."version (version) values ('3.2.0')"); 
 } 
 if(!plugin_exsists('slideshow')){
  $long_name = 'Slideshow Manager';
  $shortname = 'slideshow';
  $description = 'Manager for Slideshow in Home Page.';
  $version = '1.0.0'; 
  $bc_version_required = '3.0.0';
   execute('insert into '.$table_prefix.'plugins (name,shortname,version,description,min_bc_version_required,system) values ("'.$long_name.'","'.$shortname.'","'.$version.'","'.$description.'","'.$bc_version_required.'",1)'); 
  mysql_query("CREATE TABLE IF NOT EXISTS ".$table_prefix."slideshow (
	id bigint(20) NOT NULL AUTO_INCREMENT,
	name varchar(250) CHARACTER SET utf8 NOT NULL,
	active tinyint(1) NOT NULL DEFAULT 0,	
	imgs longtext CHARACTER SET utf8 NOT NULL,	  
	PRIMARY KEY (id)
  ) DEFAULT CHARSET=utf8");  
 }
?>