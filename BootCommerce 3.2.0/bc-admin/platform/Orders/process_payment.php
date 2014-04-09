<?php
require_once('../../include/inc_load.php');
require_once(rel_admin_path.'/control_login.php');
require_once('general_tags.php');
$status = $_POST['status'] == 'ToProcess' ? 1 : 0;
execute ('update '.$table_name.' set payed = '.$status.' where paypal_id_transaction = "" and id = '.$_POST['id']);
?>