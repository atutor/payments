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

define('AT_INCLUDE_PATH', '../../include/');
require (AT_INCLUDE_PATH.'vitals.inc.php');
admin_authenticate(AT_ADMIN_PRIV_ECOMM);

if(!isset($_config['ec_uri'])){
	$msg->addFeedback('EC_PAYMENTS_CONFIG_NEEDED');
	header('Location: index_admin.php');
	exit;
}
///////
// Delete a payment record after confirmation
if (isset($_POST['submit_yes'])) {
	$id = intval($_POST['id']);

	$sql = "DELETE from %spayments WHERE payment_id = %d";
	$result= queryDB($sql, array(TABLE_PREFIX, $id));
	
	if($result >0){
	    $msg->addFeedback('ACTION_COMPLETED_SUCCESSFULLY');
	}
	header('Location: '.AT_BASE_HREF.'mods/payments/payments_admin.php');
	exit;

} else if (isset($_POST['submit_no'])) {
	$msg->addFeedback('CANCELLED');
	header('Location: '.AT_BASE_HREF.'mods/payments/payments_admin.php');
	exit;
}

if(isset($_GET['delete'])){
	$hidden_vars['id'] = intval($_GET['delete']);
	require (AT_INCLUDE_PATH.'header.inc.php');
	$msg->addConfirm('DELETE_PAYMENT', $hidden_vars);
	$msg->printConfirm();
	require (AT_INCLUDE_PATH.'footer.inc.php');
	exit;
}
////////

function is_enrolled($member_id, $course_id) {
	global $db;

	$sql = "SELECT approved FROM %scourse_enrollment WHERE course_id=%d AND member_id=%d AND approved<>'n'";
	$row = queryDB($sql, array(TABLE_PREFIX, $course_id, $member_id), TRUE);

	return $row;
}

$sql	= "SELECT COUNT(*) AS cnt FROM %spayments";
$row = queryDB($sql, array(TABLE_PREFIX), TRUE);

if($row['cnt'] > 0){
	$num_results = $row['cnt'];
} else {
	require(AT_INCLUDE_PATH.'header.inc.php');
	$msg->printInfos('EC_NO_STUDENTS_ENROLLED');
	require(AT_INCLUDE_PATH.'footer.inc.php');
	exit;
}

$results_per_page = 25;
$num_pages = max(ceil($num_results / $results_per_page), 1);
$page = abs($_GET['p']);

if (!$page) {
	$page = 1;
}

$count  = (($page-1) * $results_per_page) + 1;
$offset = ($page-1)*$results_per_page;

// enroll/unenroll students

if($_GET['func'] == 'enroll'){

	$_GET['func']   = $addslashes($_GET['func']);
	$sql = "REPLACE INTO %scourse_enrollment SET approved = 'y' WHERE course_id= %d AND member_id = %d";
	$result = queryDB($sql,array(TABLE_PREFIX, $_GET['course_id'], $_GET['id0']));
	
}else if($_GET['func'] == 'unenroll'){

	$_GET['func']   = $addslashes($_GET['func']);
	$sql = "REPLACE INTO %scourse_enrollment SET approved = 'n' WHERE course_id= %d AND member_id = %d";
	$result = queryDB($sql,array(TABLE_PREFIX, $_GET['course_id'], $_GET['id0']));
}

/// Get a list of those who have made payments
if ($_GET['reset_filter']) {
	unset($_GET);
}

$page_string = '';

$sql = "SELECT P.*, M.login FROM %spayments P INNER JOIN %smembers M USING (member_id)   ORDER BY  timestamp desc LIMIT %d, %d";
$rows_payments = queryDB($sql, array(TABLE_PREFIX, TABLE_PREFIX, $offset, $results_per_page));

require (AT_INCLUDE_PATH.'header.inc.php'); 
	print_paginator($page, $num_results, $page_string, $results_per_page); 

?>
	
	<table class="data static" summary="">
	<thead>
	<tr>
		<th scope="col"><?php echo _AT('date'); ?></th>
		<th scope="col"><?php echo _AT('login_name'); ?></th>
		<th scope="col"><?php echo _AT('course'); ?></th>
		<th scope="col"><?php echo _AT('enrolled'); ?></th>
		<th scope="col"><?php echo _AT('ec_payment_made'); ?></th>
		<th scope="col"><?php echo _AT('ec_transaction_id'); ?></th>
		<th scope="col"></th>
	</tr>
	</thead>
	<?php   foreach($rows_payments as $row){
				$payment_count++;
			if(is_int($payment_count/2)){
				$rowcolor = "even";	
			} else{
				$rowcolor = "odd";
			}
	?>
	<tr class="<?php echo $rowcolor; ?>">
		<td align="center"><?php echo $row['timestamp']; ?></td>
		<td align="center"><?php echo $row['login']; ?></td>
		<td align="center"><?php echo $system_courses[$row['course_id']]['title']; ?></td>
		<td align="center">
		<?php 
			if($row['approved'] == '2'){
				echo _AT('na');
			}else{
			if (is_enrolled($row['member_id'], $row['course_id'])): 
					echo _AT('yes').' - <a href="mods/_core/enrolment/admin/enroll_edit.php?id0='.$row['member_id'].SEP.'func=unenroll'.SEP.'tab=0'.SEP.'course_id='.$row['course_id'].'">'._AT('unenroll').'</a>';
			else:
					echo _AT('no').' - <a href="mods/_core/enrolment/admin/enroll_edit.php?id0='.$row['member_id'].SEP.'func=enroll'.SEP.'tab=0'.SEP.'course_id='.$row['course_id'].'">'._AT('enroll').'</a>';
			endif; 
			}
		?>
		</td>
		<td align="center"><?php echo $_config['ec_currency_symbol'].number_format($row['amount'], 2); ?> <?php echo $_config['ec_currency']; ?></td>
		<td align="center"><?php echo $row['transaction_id']; ?></td>
			<td align="center"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?delete=<?php echo $row['payment_id']; ?>	"><?php echo _AT('delete'); ?></a></td>
	</tr>
	<?php 
	} // end foreach $rows_payments
	?>
	</table>

<?php require (AT_INCLUDE_PATH.'footer.inc.php'); ?>