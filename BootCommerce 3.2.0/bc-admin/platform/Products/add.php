<?php
require_once('../../include/inc_load.php');
require_once(rel_admin_path.'/control_login.php');
require_once('general_tags.php');
require_once(rel_client_path.'/include/lib/Zebra_Mptt.php');
$mptt = new Zebra_Mptt();
 $error_alert = '';
 $error_cod = '';
 $random_code = random_cod(10);		
 
  $name = $_POST['name'];
  $description = $_POST['description'];
  $code = $_POST['code'];
  $price = $_POST['price'];
  $offer = $_POST['offer'];
  $tax = isset($_POST['tax']) ? $_POST['tax'] : 0;
  $price_with_tax = 0;
  if(plugin_exsists('multitaxes')){
	$multitax_pl = isset($_POST['multitax']) ? implode(',',$_POST['multitax']) : '';
  }  
  if($_POST['price_type'] == '1'){
	 $price = $price/((100+$tax)/100);
	 $offer = $offer/((100+$tax)/100);
	 $price_with_tax = 1;
  }   
  $availability = $_POST['availability'];
  $category = $_POST['category'];
  $by_exposure = isset($_POST['by_exposure']) ? 1 : 0;
  $active = isset($_POST['visible']) ? 1 : 0;
  $showcase = isset($_POST['showcase']) ? 1 : 0;
  $unlimited_availability = isset($_POST['unlimited_availability']) ? 1 : 0;  
/***************** ontrol on duplicate codes ***********/	
$rs_control_result = execute('select code,categories from '.$table_name.' where code = "'.str_db($code).'" and categories = "'.str_db($category).'"');	  
$rs_control = mysql_fetch_array($rs_control_result);
 if($rs_control){
  $error_alert .= $lang_['products']['INSERT_UPDATE_DUPLICATE_ITEM_ERROR'].'<br/>';
 }
/***************** /ontrol on duplicate codes ***********/

/***************** upload ***********/
if(!isset($_FILES)) $_FILES = $HTTP_POST_FILES;
 $upload_dir = path_rel_img_products;
 $ext_error = '';
 $generic_error = '';
 $allowed_types = array ("jpg","png","jpeg");
  foreach($_FILES as $key => $val){
	if(!empty($_FILES[$key]["name"])){
	  $new_name = $key.'_'.$random_code;
	  $ext = explode('.',$_FILES[$key]["name"]);
	  $get_id = explode('_',$key);
	  $file_name = end($get_id).'_'.$random_code.'.'.mb_strtolower(end($ext));
	  if(!in_array(mb_strtolower(end($ext)),$allowed_types)) {
	   $ext_error .= 't';
	  }
	  if(!@is_uploaded_file($_FILES[$key]["tmp_name"])) $generic_error .= 't';
	  if(end($get_id) == 1){	
	   $img_url = $random_code.'.'.mb_strtolower(end($ext));
	  }
	  $array_filename[] = array(
	      "tempname" => $_FILES[$key]["tmp_name"],
		  "filename" => $file_name,
		  "ext" => mb_strtolower(end($ext))
	  );
	  $array_img[end($get_id)] = array(
		"urlimg" => $file_name,
		"principale" => end($get_id) == 1 ? 1 : 0
	  );		  
	}
  } 
 if($ext_error != '') $error_alert .= str_replace('{allowed_files}',implode(",", $allowed_types),$lang_['products']['UPLOAD_EXTENSION_FILES_ERROR']).'<br/>';
 if($generic_error != '') $error_alert .= $lang_['products']['UPLOAD_GENERIC_ERROR'];
/***************** / upload ***********/					
 if($error_alert != ''){
  echo '<div class="error_alert">'.$error_alert.'</div>';
  exit();
 }else{ 
 /* GET STATUS BASED ON CATEGORY STATUS */
 $sql_cat = execute('select status from '.$table_prefix.'categories where id = '.$category);
 $rs_cat = mysql_fetch_array($sql_cat);
 $visible = $rs_cat['status'] ? 1 : 0;
 /* ATTRIBUTES */     
  $record = 'images,url_image,name,file_name,categories,code,price,offer,tax,price_with_tax,description,attributes,options,add_data,by_exposure,unlimited_availability,';
  $record .= 'units,meta_title,meta_keywords,meta_description,visible,availability,';
  if(plugin_exsists('multitaxes')){
	$record .= 'pl_multitax,';  
  }  
  $record .= 'active,showcase';
	foreach($_POST as $key => $val){
	  if(mb_substr($key,0,10) == 'nattribute'){
		 $get_arr_position = explode('_',$key);
		 $get_position = end($get_arr_position);
		 if(str_replace(' ','',$val) != '' && str_replace(' ','',$_POST['vattribute_'.$get_position]) != '')
		 $arr_attributes[] = array("attribute_name" => str_db($val),"attribute_value" => str_db($_POST['vattribute_'.$get_position]),"asfilter" => (isset($_POST['asfilter_'.$get_position]) ? 1 : 0));
	  }
	}
  /* OPTIONS */	
	$arr_options = array();
	if(isset($_POST['noption'])){
	  foreach($_POST['noption'] as $key => $val){
		  if(str_replace(' ','',$val['name']) != '' && isset($_POST['noption'][$key]['voption'])){
		   $array_option_value = array();
		   $option_code = random_cod(5);
		   if(!isset($_POST['noption'][$key]['required_option'])){
			$option_required = 0;
		   }else{
			$option_required = 1;
		   }
			 foreach($_POST['noption'][$key]['voption'] as $k => $v){
			   $array_option_value[random_cod(5)] = array(
														   "value" => str_db($v['value']),
														   "price" => str_db($v['price']),
														   "type" => str_db($v['type'])
														 );										 
			 }	
			  $arr_options[$option_code] = array(		                    
								  "name" => str_db($val['name']),
								  "required_option" => $option_required,
								  "voption" => $array_option_value
								  );	 			  
		  }
	  }	 
	}
	 	
      if(!empty($array_img)){
		$val = "'".serialize(str_serialize($array_img))."',";
		$val .= "'".str_db($img_url)."',";
	  }else{
		$val = "'',";
		$val .= "'',";	 
	  }
		$val .= "'".str_db($name)."',";
		$val .= "'".filesystem($name)."',";
		$val .= "'".str_db($category)."',";	
		$val .= "'".str_db($code)."',";
		$val .= "'".str_db($price)."',";
		$val .= "'".str_db($offer)."',";
		$val .= "'".str_db($tax)."',";
		$val .= "'".str_db($price_with_tax)."',";
		$val .= "'".str_db_content($description)."',";
      if(!empty($arr_attributes)){
		$val .= "'".serialize(str_serialize($arr_attributes))."',";
	  }else{
		$val .= "'',";	 
	  }		
      if(!empty($arr_options)){
		$val .= "'".serialize(str_serialize($arr_options))."',";
	  }else{
		$val .= "'',";	 
	  }		  
	    $val .= "'".date("Y-m-d H:i:s")."',";
		$val .= "'".$by_exposure."',";
		$val .= "'".$unlimited_availability."',";
		$val .= "'".str_db($_POST['units'])."',";		
		if(str_replace(' ','',$_POST['meta_title']) != ''){
		 $val .= "'".str_db($_POST['meta_title'])."',";
		}else{
		  $val .= "'',";
		}		 
		if(str_replace(' ','',$_POST['meta_keywords']) != ''){
		 $val .= "'".str_db($_POST['meta_keywords'])."',";
		}else{
		  $val .= "'',";
		}		 
		if(str_replace(' ','',$_POST['meta_description']) != ''){
		 $val .= "'".str_db(strip_tags(preg_replace('/\s+/', ' ',$_POST['meta_description'])))."',";
		}else{
		  $val .= "'',";
		}
		$val .= "'".$visible."',";
		$val .= "'".str_db($availability)."',";		
		if(plugin_exsists('multitaxes')){
			$val .= "'".str_db($multitax_pl)."',";
		}
		$val .= "'".$active."',";
		$val .= "'".$showcase."'";
		$sql = " insert into ".$table_name." (";
		$sql .= $record;
		$sql .= ") VALUES (";
		$sql .=  $val;
		$sql .=  ")";  
		execute($sql);
		$last_id = mysql_insert_id();
		if(!empty($array_filename)){
		 foreach($array_filename as $key => $val){
		   upload_resize_img($val['tempname'],$val['filename'],'600,300,70,50',$upload_dir.'/'.$last_id,true);		
		 }
		}
	    /********** create idex files for this product ****************/ 	
		$dir = $mptt->get_orizzontal_path($category, '/');
		$count_subdirectories = count(explode('/',$dir));	
			  if(!file_exists(path_rel_products.'/'.$dir.'/'.$last_id.'-'.filesystem($name).'.php')){
					  $file = fopen(path_rel_products.'/'.$dir.'/'.$last_id.'-'.filesystem($name).'.php', "w+") or exit("Error!");	
					  $control = 'require_once('.str_repeat('dirname(',$count_subdirectories+3).'(__FILE__)'.str_repeat(')',$count_subdirectories+3).'.\'/include/inc_load.php\');'."\n";			  					  
					  $control .= '$product_id = '.$last_id.';'."\n";
					  $control .= '$category_id = '.$category.';'."\n";		  
					  $control .= 'require_once(theme_rel_path.\'/product.php\');'."\n";	
					  fwrite($file,'<'.'?php'."\n");	
					  fwrite($file,$control);			   
					  fwrite($file,'?'.'>');
					  fclose($file);	
                      chmod(path_rel_products.'/'.$dir.'/'.$last_id.'-'.filesystem($name).'.php', 0755);						  
			  } 
			  if(!file_exists(path_rel_products.'/'.$last_id.'-'.filesystem($name).'.php')){
					  $file = fopen(path_rel_products.'/'.$last_id.'-'.filesystem($name).'.php', "w+") or exit("Error!");				  
					  $control = 'require_once('.str_repeat('dirname(',3).'(__FILE__)'.str_repeat(')',3).'.\'/include/inc_load.php\');'."\n";				  
					  $control .= '$product_id = '.$last_id.';'."\n";		  
					  $control .= 'require_once(theme_rel_path.\'/product.php\');'."\n";	
					  fwrite($file,'<'.'?php'."\n");	
					  fwrite($file,$control);			   
					  fwrite($file,'?'.'>');
					  fclose($file);
                      chmod(path_rel_products.'/'.$last_id.'-'.filesystem($name).'.php', 0755);					  
			  } 			  			 
			  /** write array products for each root directory of this new category **/
			  foreach($mptt->get_path($category) as $key => $val){
				$dir = $mptt->get_orizzontal_path($val['id'], '/');
				if(file_exists(path_rel_products.'/'.$dir.'/inc_array_product.php')){
					require(path_rel_products.'/'.$dir.'/inc_array_product.php');			  
					 if(!in_array($last_id,$arr_container_products)){
						 $arr_container_products[] = $last_id;
					 }
				}else{
						 $arr_container_products = array($last_id);
				}		  
					$file = fopen(path_rel_products.'/'.$dir.'/inc_array_product.php', "w+") or exit("Error!");
					fwrite($file,'<'.'?php'."\n");	
					fwrite($file,'$arr_container_products = array('.implode(',',$arr_container_products).');'."\n");			   
					fwrite($file,'?'.'>');
					fclose($file); 
					chmod(path_rel_products.'/'.$dir.'/inc_array_product.php', 0755);
			  }
   /* save attributes like filters */	
    $var_attributes = '';
	if(!empty($arr_attributes)){
		foreach($arr_attributes as $key => $val){	  
			 if($val['asfilter'])
			 $var_attributes .= '('.$last_id.',"'.$val['attribute_name'].'","'.$val['attribute_value'].'"),';  	 
		}
	}
	if($var_attributes != ''){
		$var_attributes = mb_substr($var_attributes,0,-1);
		execute('insert into '.$table_prefix.'products_attributes (id_product,attribute_name,attribute_value) values '.$var_attributes);
	}
 }
?>