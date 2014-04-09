<?php
error_reporting(E_ALL ^ E_NOTICE);
require_once('include/inc_load.php');
// devdaily.com paypal php ipn example.
// version 1.0
// this example built on the paypal php ipn example, with bug fixes,
// and no need for ssl.

// read the post from paypal and add 'cmd'
$req = 'cmd=_notify-validate';
$get_magic_quotes_exits = function_exists('get_magic_quotes_gpc') ? true : false;
$fsockopen_exits = function_exists("fsockopen") ? true : false;	
$arr_result = array();
// handle escape characters, which depends on setting of magic quotes
foreach ($_POST as $key => $value){
  if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1){
    $value = urlencode(stripslashes($value));
  }else{
    $value = urlencode($value);
  }
  $req .= "&$key=$value";  
  $arr_result[$key] = $value;
}

// post back to paypal to validate
$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
$paypal_url = $paypal['sendbox'] ? 'www.sandbox.paypal.com' : 'www.paypal.com';
/*if($fsockopen_exits == true){	
 if($paypal_ssl) $fp = fsockopen ('ssl://'.$paypal_url, 443, $errno, $errstr, 30);	
 else $fp = fsockopen ($paypal_url, 80, $errno, $errstr, 30);
 if (!$fp) {
	 exit();
 }else{
	 fputs ($fp, $header . $req);
	  while (!feof($fp)) {
		  $res = fgets ($fp, 1024);
		  if (strcmp ($res, "VERIFIED") == 0) $verified = true;
		  if (strcmp ($res, "INVALID") == 0) $verified = false;  
	  }
	  fclose($fp);	 
 }
}else{*/
	$url= 'https://'.$paypal_url.'/cgi-bin/webscr';
	$curl_result=$curl_err='';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded", "Content-Length: " . strlen($req)));
	curl_setopt($ch, CURLOPT_HEADER , 0);
	curl_setopt($ch, CURLOPT_VERBOSE, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	$res = curl_exec($ch);
	$curl_err = curl_error($ch);
	curl_close($ch);
		  if (strcmp ($res, "VERIFIED") == 0){
		      $error = '';
			  if (mb_strtolower($_POST['receiver_email']) != $paypal['email'])  $error .= $lang_client_['ipn_paypal']['WRONG_RECEIVER_EMAIL'].'<br/>';  
			  $sql = execute('select count(*) as count from '.$table_prefix.'orders where paypal_id_transaction = "'.str_db($_POST['txn_id']).'"'); 
			  $rs = mysql_fetch_array($sql);
			  if($rs['count'] > 0) $error .= $lang_client_['ipn_paypal']['TRANSACTION_ALREADY_PROCESSED'].'<br/>';
			  $sql = execute('select grandtotal from '.$table_prefix.'orders where code_order = "'.str_db($_POST['transaction_subject']).'"'); 
			  $rs = mysql_fetch_array($sql);	
			  if ($_POST['mc_gross'] != @number_format($rs['grandtotal'],2,'.','')) $error .= $lang_client_['ipn_paypal']['WRONG_TRANSACTION_AMOUNT'];
			  if($error == ''){		 
				  $val = "paypal_array = '".serialize(str_serialize($arr_result))."',";
				  $val .= "paypal_status = '".str_db($_POST['payment_status'])."',";
				  if(mb_strtolower($_POST['payment_status']) == 'completed') $val .= "payed = 1,";
				  $val .= "paypal_id_transaction = '".str_db($_POST['txn_id'])."'";
				$sql = "update ".$table_prefix."orders set ".$val." where code_order = '".$_POST['transaction_subject']."'";
				 execute($sql);		
			  }			  
		  }
		  //if (strcmp ($res, "INVALID") == 0){
			  
		  //}     
//}
?>