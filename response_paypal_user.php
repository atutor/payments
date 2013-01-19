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
$transaction_id = $addslashes($_REQUEST['tx']);
$payment_id = intval($_REQUEST['item_number']);

if ($_GET['st'] == "Completed"){

	approve_payment($payment_id, $transaction_id);
	$msg->addFeedback('ACTION_COMPLETED_SUCCESSFULLY');

} else if ($_GET['st'] == "Pending"){
	approve_payment($payment_id, $transaction_id);
	$msg->addFeedback('ACTION_COMPLETED_SUCCESSFULLY');
	$msg->addFeedback('ACTION_PENDING_CC_CONFIRM');

}else {
	$msg->addError('EC_PAYMENT_FAILED');
}
unset($_SESSION['payment_id']);
unset($_SESSION['seats_requested']);
//print_r($_GET);
header('Location: index.php');
exit;
?>