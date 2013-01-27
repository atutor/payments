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

$_user_location	= 'public';
define('AT_INCLUDE_PATH', '../../include/');
require(AT_INCLUDE_PATH.'vitals.inc.php');
require('include/payments.lib.php');

// read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-validate';

foreach ($_POST as $key => $value) {
	$value = urlencode($stripslashes($value));
	$req .= "&$key=$value";
		//log_paypal_ipn_requests($req);
}

$host = parse_url($_config['ec_uri']);
$host = $host['host']; // either www.sandbox.paypal.com or just www.paypal.com
if (strcasecmp($host, 'www.sandbox.paypal.com') && strcasecmp($host, 'www.paypal.com')) {
	// don't want to post this to the wrong URI
	exit;
}

// post back to PayPal system to validate
$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
$fp = fsockopen($host, 80, $errno, $errstr, 30);
if (!$fp) { exit; }

$result = '';
unset($error);
	//log_paypal_ipn_requests( $req);
fputs($fp, $header . $req);
while (!feof($fp)) { 
	$result .= fgets($fp, 1024);
}
		//log_paypal_ipn_requests("RESULT: ".$result);
if (strcmp ($result, "VERIFIED") == 0) {
	$ppmsg .= " Verified: ";
} else if (strcasecmp($_POST['payment_status'], 'Completed') == 0) {
	$ppmsg .= " Completed: ";
} else {
	$ppmsg .= " NOT VERIFIED ";
	//log_paypal_ipn_requests( $ppmsg . $result);
	$error = 2;
}


$_POST['item_number'] = $addslashes($_POST['item_number']);
$_POST['txn_id']      = $addslashes($_POST['txn_id']);

// check that txn_id has not been previously processed
$sql = "SELECT transaction_id, amount FROM ".TABLE_PREFIX."payments WHERE payment_id='$_POST[item_number]'";
$result2 = mysql_query($sql, $db);
if (!($row = mysql_fetch_assoc($result2))) {
	// Error: no valid payment_id
	$ppmsg .= " No PaymentID ";
	$error = 4;
} else if ($row['transaction_id']) {
	// Error: this transaction has already been processed
	$ppmsg .= " Duplicate ";
	$error = 5;
} else if ($row['amount'] != $_POST['mc_gross']) {
	// Error: wrong amount sent
	$ppmsg .= " Mismatched Amounts ";
	$error = 6;
} else if ($_config['ec_currency'] != $_POST['mc_currency']) {
	// Error: wrong currency
	$ppmsg .= " Wrong Currency ";
	$error = 7;
}

if (!isset($error)) {
	approve_payment($_POST['item_number'], $_POST['txn_id']);
	$status = "VALID ($error)";
	log_paypal_ipn_requests($status . $result . $ppmsg);
} else {
	$status = "INVALID ($error)";
	log_paypal_ipn_requests($status . $result . $ppmsg);
}

?>