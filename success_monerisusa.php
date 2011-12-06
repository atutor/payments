<?php
/************************************************************************/
/* ATutor																*/
/************************************************************************/
/* Copyright (c) 2002-2010                                              */
/* Inclusive Design Institute                                           */
/* http://atutor.ca                                                     */
/* This program is free software. You can redistribute it and/or        */
/* modify it under the terms of the GNU General Public License          */
/* as published by the Free Software Foundation.                        */
/************************************************************************/

$_user_location	= 'users';
define('AT_INCLUDE_PATH', '../../include/');
require(AT_INCLUDE_PATH.'vitals.inc.php');
require('include/payments.lib.php');

$amount = floatval($_POST['amount']);
$id = intval($_POST['rvar_course_id']);
$approved = intval($_POST['auth_code']);
$ordernumber = intval($_POST['rvar_payment_id']);
$trans_id = $addslashes($_POST['order_no']);
$course_id = intval($_POST['rvar_course_id']);

if ($_config['ec_contact_email']){
	$contact_admin_email = $_config['ec_contact_email'];
} else {
	$contact_admin_email = $_config['contact_email'];
}

if($approved > 1 && $trans_id){
	approve_payment($ordernumber,$trans_id);
}else{

	$msg->addError('EC_PAYMENT_FAILED');
}
log_requests($trans_id);

unset($_SESSION['payment_id']);
require (AT_INCLUDE_PATH.'header.inc.php');


require (AT_INCLUDE_PATH.'footer.inc.php');
?>
