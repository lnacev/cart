<?php
  $is_included = get_included_files();
  if( $is_included[0] == (__FILE__) ) die('You have no permission for direct access to this file');
  require_once(dirname(dirname(dirname(__FILE__))).'/include/inc_load.php');  
 if(!plugin_exsists('multitaxes')){
  mysql_query("CREATE TABLE IF NOT EXISTS ".$table_prefix."taxes (
	id bigint(20) NOT NULL AUTO_INCREMENT,
	name varchar(250) CHARACTER SET utf8 NOT NULL,
	percentage decimal(65,10) NOT NULL,		  
	PRIMARY KEY (id)
  ) DEFAULT CHARSET=utf8");
  mysql_query("ALTER TABLE ".$table_prefix."products ADD pl_multitax longtext CHARACTER SET utf8 NOT NULL"); 
  mysql_query("ALTER TABLE ".$table_prefix."orders ADD pl_multitax_array longtext CHARACTER SET utf8 NOT NULL"); 
  $long_name = 'Multi Taxes';
  $shortname = 'multitaxes';
  $description = 'This plugin allows you to have more than a tax on every product.';
  $version = '1.0.0'; 
  $bc_version_required = '3.0.0';
   execute('insert into '.$table_prefix.'plugins (name,shortname,version,description,min_bc_version_required) values ("'.$long_name.'","'.$shortname.'","'.$version.'","'.$description.'","'.$bc_version_required.'")');  
   execute("update ".$table_prefix."version set version = '3.1.0'");
 }
?>