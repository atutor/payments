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
require (AT_INCLUDE_PATH.'vitals.inc.php');
require ('include/payments.lib.php');

require (AT_INCLUDE_PATH.'header.inc.php');

function is_enrolled($member_id, $course_id) {

	$sql = "SELECT approved FROM %scourse_enrollment WHERE course_id=%d AND member_id=%d AND approved<>'n'";
	$rows_enrollments_approved = queryDB($sql,array(TABLE_PREFIX, $course_id, $member_id));

	return $rows_enrollments_approved;
}

/// Get a list of enrolled courses or pending enrollments, and display their fee payment status 
$payment_count = 0; // num listed courses

$sql = "SELECT course_id, approved FROM %scourse_enrollment WHERE member_id=%d && role <> 'Instructor'";
$rows_enrollments = queryDB($sql, array(TABLE_PREFIX, $_SESSION['member_id']));

if(count($rows_enrollments) > 0){?>
    <h3><?php echo _AT('enrollment'); ?></h3>
	<table class="data static" rules="rows" summary="">
	<thead>
		<tr>
			<th scope="col"><?php echo _AT('course'); ?></th>
			<th scope="col"><?php echo _AT('ec_this_course_fee'); ?></th>
			<th scope="col"><?php echo _AT('ec_payment_made'); ?></th>
			<th scope="col"><?php echo _AT('ec_enroll_approved'); ?></th>
			<th scope="col"><?php echo _AT('date'); ?></th>
			<th scope="col"><?php echo _AT('ec_action'); ?></th>
		</tr>
	</thead>
	<?php
	    foreach($rows_enrollments as $row){
			if ($_SESSION['member_id'] == $system_courses[$row['course_id']]['member_id']) {
				continue; // this member is the course instructor so ignore enrollment
			}

			$sql2 = "SELECT course_fee FROM  %sec_course_fees WHERE course_id=%d AND course_fee > '0'";
			$this_course_fee = queryDB($sql2, array(TABLE_PREFIX, $row['course_id']), TRUE);
					
			if (count($this_course_fee) > 0) {
				$this_course_fee = $this_course_fee['course_fee'];
			}
			
			$sql3 = "SELECT amount, timestamp, approved, transaction_id FROM  %spayments WHERE course_id=%d AND member_id = %d";
			$this_course_pid = queryDB($sql3, array(TABLE_PREFIX, $row['course_id'], $_SESSION['member_id']), TRUE);	

			if (count($this_course_pid) > 0 && $this_course_pid['transaction_id'] != '') {
				$this_course_paid = $this_course_pid['amount'];
				$this_payment_date = $this_course_pid['timestamp'];
				$this_enrollment_approved = $rows_enrollments['approved'];
				$this_transaction = $this_course_pid['transaction_id'];
			} else{
				$this_course_paid = '0.00';
			}

			$payment_count++;
			
			if(is_int($payment_count/2)){
				$rowcolor = "odd";	
			} else{
				$rowcolor = "even";
			}
			
			echo '<tr class="'.$rowcolor.'">';
			if(is_enrolled($_SESSION['member_id'], $row['course_id'])){
				echo '<td><a href="bounce.php?course='.$row['course_id'].'">'.$system_courses[$row['course_id']]['title'].'</a></td>';
			}else{
				echo '<td>'.$system_courses[$row['course_id']]['title'].'</td>';
			}
			
			echo '<td align="center">'.$_config['ec_currency_symbol'].number_format($this_course_fee, 2).' '.$_config['ec_currency'].'</td>';
			echo '<td align="center">'.$_config['ec_currency_symbol'].number_format($this_course_paid , 2).' '.$_config['ec_currency'].'</td>';
			
			if ($row['approved'] == 'y'){
				echo '<td align="center">'._AT('yes').'</td>';		
			} else {
				echo '<td align="center">'._AT('no').'</td>';

			}
			 
			echo '<td align="center">'.$this_payment_date.'</td>';
		
			if ($row['approved'] == 'y'){
				echo '<td><a href="bounce.php?course='.$row['course_id'].'">'._AT('login').'</a></td>';			
			}else if ($this_course_pid['transaction_id'] != '' && $row['approved'] != 'y'){
                echo '<td>'._AT('pending_approval').'</td>';
			}else{
				echo '<td align="center"><a href="mods/payments/payment.php?course_id='.$row['course_id'].'">'._AT('ec_make_payment').'</a></td>';
			}
			echo '</tr>';
		}	
		echo '</table><p><br /></p>';
		if($payment_count == 0){
			$msg->printInfos('EC_NO_PAID_COURSES');
		}
}

$sql = "SELECT * from %spayments WHERE member_id=%d ORDER BY timestamp DESC";
$rows_payments = queryDB($sql, array(TABLE_PREFIX, $_SESSION['member_id']));

if(count($rows_payments) > 0){
?>
	<table class="data static" rules="rows" summary="">
	<thead>
		<tr>
			<th scope="col"><?php echo _AT('course'); ?></th>
			<th scope="col"><?php echo _AT('ec_fees'); ?></th>
			<th scope="col"><?php echo _AT('ec_payment_made'); ?></th>
			<th scope="col"><?php echo _AT('ec_enroll_approved'); ?></th>
			<th scope="col"><?php echo _AT('date'); ?></th>
			<th scope="col"><?php echo _AT('ec_action'); ?></th>
		</tr>
	</thead>
	<?php
	    foreach($rows_payments as $row){
			$this_course_fee = $row['amount'];
			$payment_count++;
			if(is_int($payment_count/2)){
				$rowcolor = "even";	
			} else{
				$rowcolor = "odd";
			}

		    if(count($rows_payments) > 0){
				echo '<tr class="'.$rowcolor.'">';
				if ($row['approved'] == '2'){
				    echo '<td><a href="bounce.php?course='.$row['course_id'].'">'.$system_courses[$row['course_id']]['title'].'</a></td>';		
				}else{
				    echo '<td>'.$system_courses[$row['course_id']]['title'].'</td>';		
			    }
				
				echo '<td align="center">'.$_config['ec_currency_symbol'].number_format($this_course_fee, 2).' '.$_config['ec_currency'].'</td>';

				if ($row['transaction_id'] != ''){
					echo '<td align="center">'.$_config['ec_currency_symbol'].number_format($row['amount'], 2).' '.$_config['ec_currency'].'</td>';
				} else if ($row['approved'] == 0){
					echo '<td align="center">0.00</td>';
				} 
				
				if (is_enrolled($_SESSION['member_id'], $row['course_id'])){
				    echo '<td align="center">'._AT('yes').'</td>';
				}else{
				    echo '<td align="center">'._AT('no').'</td>';
				}
				
				
				echo '<td align="center">'.$row['timestamp'].'</td>';

				if ($row['transaction_id'] != ''){
					echo '<td align="center">'._AT('ec_full_payment_recieved').'</td>';
				}else{
					echo '<td align="center"><a href="mods/payments/payment.php?course_id='.$row['course_id'].'">'._AT('ec_make_payment').'</a></td>';
				}
				echo '</tr>';
			}
		}	
		echo '</table>';
		if($payment_count == 0){
			$msg->printInfos('EC_NO_PAID_COURSES');
		}
} else {
?>
	<table class="data static" rules="rows" summary="">
	<thead>
		<tr>
			<th scope="col"><?php echo _AT('course'); ?></th>
			<th scope="col"><?php echo _AT('ec_fees'); ?></th>
			<th scope="col"><?php echo _AT('ec_payment_made'); ?></th>
			<th scope="col"><?php echo _AT('ec_enroll_approved'); ?></th>
			<th scope="col"><?php echo _AT('date'); ?></th>
			<th scope="col"><?php echo _AT('ec_action'); ?></th>
		</tr>
	</thead>
	<tr>
	<td> None found
	</td>
	</tr>
	</table>
<?php
}


require (AT_INCLUDE_PATH.'footer.inc.php'); ?>