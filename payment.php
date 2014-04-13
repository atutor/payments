<?php
/************************************************************************/
/* ATutor																*/
/************************************************************************/
/* Copyright (c) 2002 - 2014                                            */
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

if ($_POST['cancel']) {
	header('location: index.php');
	exit;
}


$course_id = intval($_REQUEST['course_id']);
$member_id = intval($_SESSION['member_id']);
if(isset($_POST['seats_requested'])){
	$seats_requested = intval($_POST['seats_requested']);
}

// check if this is a seats purchase
if(!isset($seats_requested)){
	$sql = "SELECT course_fee FROM %sec_course_fees WHERE course_id=%d";
	$this_course_fee = queryDB($sql, array(TABLE_PREFIX, $course_id), TRUE);

	if(count($this_course_fee) >0){
		$this_course_fee = $this_course_fee['course_fee'];
	} else {
		header('location: index.php');
		exit;
	}
	
	///Check if a partial payment has already been made so the balance can be calculated
	$sql4 = "SELECT SUM(amount) AS total_amount FROM %spayments WHERE course_id=%d AND approved=1 AND member_id=%d";
	$rows_amounts = queryDB($sql4, array(TABLE_PREFIX, $course_id, $member_id));

    foreach($rows_amounts as $row4){
		if($row4['total_amount'] > 0){
			$amount_paid = $row4['total_amount'];
		} else {
			$amount_paid = 0.00;
		}
	}
	$balance_course_fee = $this_course_fee - $amount_paid;
	$this_course_fee = $balance_course_fee;
} else if(isset($seats_requested)){

	$balance_course_fee = number_format(($_config['seat_price']*$seats_requested), 2);
	$this_course_fee = $balance_course_fee;
}

require (AT_INCLUDE_PATH.'header.inc.php');

if($_SESSION['payment_id']){
    $sql = "REPLACE INTO %spayments VALUES (%d, NULL, 0, '', %d, %d, '%s')";
    $result = queryDB($sql, array(TABLE_PREFIX, $_SESSION['payment_id'], $_SESSION['member_id'], $course_id, $balance_course_fee));
} else {
    $sql = "INSERT INTO %spayments VALUES ('', NULL, 0, '', %d, %d, '%s')";
    $result = queryDB($sql, array(TABLE_PREFIX, $_SESSION['member_id'], $course_id, $balance_course_fee));
}

if(!isset($_SESSION['payment_id'])){
	$payment_id = at_insert_id();
	$_SESSION['payment_id'] = $payment_id;
} else{
	$payment_id = $_SESSION['payment_id'];
}
if(isset($seats_requested)){
	$_SESSION['seats_requested'] = $seats_requested;
}

?>
<div class="input-form">
	<div class="row">
		<h3><?php echo _AT('confirm'); ?></h3>

		<p><?php echo _AT('ec_confirm_info'); ?></p>
		<dl>
			<dt><?php echo _AT('ec_course');?></dt>
			<dd><?php echo $system_courses[$course_id]['title']; ?></dd>
		
<?php 	//course seats purchase	
		if(isset($seats_requested)){ ?>
			<dt><?php echo _AT('ec_course_seats');?></dt>
			<dd><?php echo $seats_requested; ?></dd>
			<dt><?php echo _AT('ec_course_seats_price');?></dt>
			<dd><?php echo $_config['ec_currency_symbol'].number_format($_config['seat_price'],2); ?></dd>
			<dt><?php echo _AT('ec_balance_due');?></dt>
			<dd><?php echo $_config['ec_currency_symbol'].number_format(($_config['seat_price']*$seats_requested), 2).' '.$_config['ec_currency'];?></dd>
<?php }else{ // course enrollment fees purchase ?>

			<dt><?php echo _AT('ec_this_course_fee');?></dt>
			<dd><?php echo $_config['ec_currency_symbol'].number_format($this_course_fee,2).' '.$_config['ec_currency'];?></dd>

			<dt><?php echo _AT('ec_amount_recieved');?></dt>
			<dd><?php echo $_config['ec_currency_symbol'].$amount_paid;?></dd>

			<dt><?php echo _AT('ec_balance_due');?></dt>
			<dd><?php echo $_config['ec_currency_symbol'].number_format($balance_course_fee, 2).' '.$_config['ec_currency'];?></dd>

<?php } ?>		
		</dl>	
		<h4><?php echo _AT('ec_requirements'); ?></h4>
		<ul>
			<li><?php echo _AT('ec_requirements_ssl'); ?></li>
			<li><?php echo _AT('ec_requirements_cookies'); ?></li>
			<li><?php echo _AT('ec_requirements_javascript'); ?></li>
			<li><?php echo _AT('ec_requirements_comments'); ?></li>
		</ul>
	</div>

	<?php
		/*
		 * these payment forms below can be replaced by any other payment gateway.
		 * when the gateway sends back the response then it is authenticated and if
		 * the amounts match then the `payments` transaction is updated and approved.
		*/
	?>

	<div class="row buttons">
		<?php monerisusa_print_form($payment_id, $balance_course_fee, $course_id); ?>
		<?php monerisca_print_form($payment_id, $balance_course_fee, $course_id); ?>
		<?php beanstream_print_form($payment_id, $balance_course_fee, $course_id); ?>
		<?php paypal_print_form($payment_id, $balance_course_fee, $course_id); ?>
		<?php mirapay_print_form($payment_id, $balance_course_fee, $course_id); ?>
		<?php check_payment_print_form($payment_id, $balance_course_fee, $course_id); ?>

	</div>
</div>
		
<?php require (AT_INCLUDE_PATH.'footer.inc.php'); ?>