<?php
/************************************************************************/
/* ATutor																*/
/************************************************************************/
/* Copyright (c) 2002 - 2013                                            */
/* ATutorSpaces                                                         */
/* https://atutorspaces.com                                             */
/* This program is free software. You can redistribute it and/or        */
/* modify it under the terms of the GNU General Public License          */
/* as published by the Free Software Foundation.                        */
/************************************************************************/

$_user_location	= 'users';
define('AT_INCLUDE_PATH', '../../include/');
require(AT_INCLUDE_PATH.'vitals.inc.php');
require('include/payments.lib.php');

$amount = floatval($_GET['trnAmount']);
$id = intval($_GET['ref2']);
$approved = intval($_GET['trnOrderNumber']);
$ordernumber = intval($_GET['trnOrderNumber']);
$trans_id = intval($_GET['trnId']);

if ($_config['ec_contact_email']){
	$contact_admin_email = $_config['ec_contact_email'];
} else {
	$contact_admin_email = $_config['contact_email'];
}

if($_GET['trnApproved'] == 1 && $_GET['trnId']){
	approve_payment($ordernumber,$trans_id);
}
log_requests($trans_id);
unset($_SESSION['payment_id']);
unset($_SESSION['seats_requested']);
require (AT_INCLUDE_PATH.'header.inc.php');
require (AT_INCLUDE_PATH.'footer.inc.php');
?>
