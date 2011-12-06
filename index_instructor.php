<?php
define('AT_INCLUDE_PATH', '../../include/');
require (AT_INCLUDE_PATH.'vitals.inc.php');

if($_SESSION['is_admin'] && $_SESSION['course_id'] >= '1'){
	authenticate(AT_PRIV_ECOMM);
}else{
	admin_authenticate(AT_ADMIN_PRIV_ECOMM);
}
if (!$_config['ec_allow_instructors']){
	require (AT_INCLUDE_PATH.'header.inc.php');
	$msg->printInfos('EC_PAYMENTS_TURNED_OFF');
	require (AT_INCLUDE_PATH.'footer.inc.php'); 
	exit;
}
if (isset($_POST['select_course'])) {
 	$_GET['course_id'] = $_POST['ec_course_id'];
 	$course_id = $_POST['ec_course_id'];
} else if ($_POST['ec_course_id']){
 	$course_id = $_POST['ec_course_id'];
} else if ($_GET['course_id']){
 	$course_id = $_GET['course_id'];
} else{
	$course_id = $_SESSION['course_id'];
}

if (isset($_POST['submit']) == "submit") {
	if($course_id == "--" || $course_id == "-1"){
	//require (AT_INCLUDE_PATH.'header.inc.php');
	$msg->addError('EC_PAYMENTS_NO_COURSE_SELECTED');
	$error = TRUE;
	//require (AT_INCLUDE_PATH.'footer.inc.php'); 
	//exit;
	}
	if(!$error){
		$_POST['ec_course_fee']   = floatval($_POST['ec_course_fee']);
		$_POST['ec_auto_approve'] = intval($_POST['ec_auto_approve']);
		$_POST['ec_auto_email']   = intval($_POST['ec_auto_email']);
		$course_id   = intval($_POST['ec_course_id']);
		
		$sql = "REPLACE INTO ".TABLE_PREFIX."ec_course_fees VALUES ('$course_id', '{$_POST['ec_course_fee']}', {$_POST['ec_auto_approve']}, {$_POST['ec_auto_email']})";
		if ($result = mysql_query($sql,$db)) {
			$msg->addFeedback('ACTION_COMPLETED_SUCCESSFULLY');
		}else{
			$msg->addError('EC_COURSE_PAYMENT_SETTINGS_NOT_SAVED');
		}
	
		header('Location: '.$_SERVER['PHP_SELF'].'?course_id='.$course_id);
		exit;
	}
}

if($_GET['func'] == 'enroll'){
	$_GET['func']   = $addslashes($_GET['func']);
	$sql = "REPLACE INTO ".TABLE_PREFIX."course_enrollment SET approved = 'y' WHERE course_id= '$_GET[course_id]' AND member_id = '$_GET[id0]'";
	$result = mysql_query($sql,$db);
}else if($_GET['func'] == 'unenroll'){

	$_GET['func']   = $addslashes($_GET['func']);
	$sql = "REPLACE INTO ".TABLE_PREFIX."course_enrollment SET approved = 'n' WHERE course_id= '$_GET[course_id]' AND member_id = '$_GET[id0]'";
	$result = mysql_query($sql,$db);
}

if(isset($course_id) && $course_id != "--" && $course_id != "-1"){
$sql = "SELECT * from ".TABLE_PREFIX."ec_course_fees WHERE course_id='$course_id'";

$result = mysql_query($sql,$db);
if ($row = mysql_fetch_assoc($result)){
	$this_course_fee   = $row['course_fee'];
	$this_auto_approve = $row['auto_approve'];
	$this_auto_email   = $row['auto_email'];
}
}
require (AT_INCLUDE_PATH.'header.inc.php');
?>
	<div class="input-form">
<?php if(admin_authenticate(AT_ADMIN_PRIV_ECOMM, TRUE) ||admin_authenticate(AT_ADMIN_PRIV_ADMIN, TRUE)){ ?>


<form action="<?php  $_SERVER['PHP_SELF']; ?>" method="post">

		<div class="row">
			<label for="ec_course_id"><?php echo _AT('ec_select_course'); ?></label><br/>
			<select name="ec_course_id">	
			<option value="--">--choose--</option>
			<?php
			$sql = "SELECT course_id, title FROM ".TABLE_PREFIX."courses ORDER BY title ASC";
			$result = mysql_query($sql, $db);
			while($row = mysql_fetch_assoc($result)){
				if($row['course_id'] == $course_id){
					echo '<option value="'.$row['course_id'].'" selected="selected">'.$row['title'].'</option>';
				}else{
					echo '<option value="'.$row['course_id'].'">'.$row['title'].'</option>';
				}
			}		
			?>
			</select>
			<input type="submit" name="select_course" value="<?php echo _AT('ec_select_course'); ?>"  class="button"/>
		</div>

<?php } ?>
</form>
<form action="<?php  $_SERVER['PHP_SELF']; ?>" method="post">
<input type="hidden" name="ec_course_id" value="<?php echo $course_id; ?>" />
		<div class="row">
			<label for="ec_course_fee"><?php echo _AT('ec_course_fee'); ?></label><br/>
			<?php echo $_config['ec_currency_symbol'] ?><input type="text" name="ec_course_fee" size="3" value="<?php echo $this_course_fee; ?>" id="ec_course_fee" size="10"  /> (<?php echo  $_config['ec_currency'] ?>)
		</div>
		<div class="row">
			<?php echo _AT('ec_auto_approve'); ?><br/>
			<input type="radio" name="ec_auto_approve" value="1" id="auto1" <?php if($this_auto_approve){ echo 'checked="checked"'; } ?>/><label for="auto1"><?php echo _AT('enable'); ?></label>
			<input type="radio" name="ec_auto_approve" value="0" id="auto0" <?php if(!$this_auto_approve){ echo 'checked="checked"'; } ?>/><label for="auto0"><?php echo _AT('disable'); ?></label>
		</div>
		<div class="row">
			<?php echo _AT('ec_auto_email'); ?><br/>
			<input type="radio" name="ec_auto_email" value="1" id="email1" <?php if ($this_auto_email) { echo 'checked="checked"'; } ?>/><label for="email1"><?php echo _AT('enable'); ?></label>
			<input type="radio" name="ec_auto_email" value="0" id="email0" <?php if (!$this_auto_email) { echo 'checked="checked"'; } ?>/><label for="email0"><?php echo _AT('disable'); ?></label>
		</div>
		
		<div class="row buttons">
			<input type="submit" name="submit" value="<?php echo _AT('save'); ?>"  class="button"/>
		</div>

</form>
</div>
<?php



//$sql2 = "SELECT  P.member_id,  P.amount, M.login FROM ".TABLE_PREFIX."payments AS P INNER JOIN ".TABLE_PREFIX."members M USING (member_id) WHERE P.course_id=$_SESSION[course_id] AND P.approved=1";


$sql2 = "SELECT  P.member_id,  P.amount, M.login FROM ".TABLE_PREFIX."payments AS P INNER JOIN ".TABLE_PREFIX."members M USING (member_id) WHERE P.course_id='$course_id'";


$result = mysql_query($sql2,$db);
if (mysql_num_rows($result)) { ?>
	<table class="data static"  rules="rows" summary="">
	<thead>
	<tr>
		<th scope="col"><?php echo  _AT('login_name'); ?></th>
		<th scope="col"><?php echo  _AT('ec_payment_made'); ?></th>
		<th scope="col"><?php echo  _AT('enrolled'); ?></th>
	</tr>
	</thead>
	<?php
		while($row = mysql_fetch_assoc($result)){
			echo '<tr>';
			if(admin_authenticate(AT_ADMIN_PRIV_ECOMM, TRUE) ||admin_authenticate(AT_ADMIN_PRIV_ADMIN, TRUE)){
				echo '<td align="center"><a href="mods/_core/users/edit_user.php?id='.$row['member_id'].'">'.$row['login'].'</a></td>';
			}else{
				echo '<td align="center"><a href="profile.php?id='.$row['member_id'].'">'.$row['login'].'</a></td>';
			}
			echo '<td align="center">'.$_config['ec_currency_symbol'].number_format($row['amount'],2).' '.$_config['ec_currency'].'</td>';
			
			$sql4 = "SELECT * from ".TABLE_PREFIX."course_enrollment WHERE course_id = '$course_id' AND member_id = '$row[member_id]'";
			if($result4 = mysql_query($sql4, $db)){
				if(admin_authenticate(AT_ADMIN_PRIV_ECOMM, TRUE) ||admin_authenticate(AT_ADMIN_PRIV_ADMIN, TRUE)){
					$enroll_manager = "mods/_core/enrolment/admin/enroll_edit.php";
				}else{
					$enroll_manager = "mods/_core/enrolment/enroll_edit.php";
				}
				if(mysql_num_rows($result4) >= '1'){
					while($row4 = mysql_fetch_assoc($result4)){
						
						if($row4['approved'] == 'y'){
							echo '<td align="center">'._AT('yes').'<small> (<a href="'.$enroll_manager.'?id0='.$row['member_id'].SEP.'func=unenroll'.SEP.'tab=0'.SEP.'course_id='.$course_id.'">'._AT('unenroll').'</a>)</small></td>';
						}else{
							echo '<td align="center">'._AT('no').' <small>( <a href="'.$enroll_manager.'?id0='.$row['member_id'].SEP.'func=enroll'.SEP.'tab=0'.SEP.'course_id='.$course_id.'">'._AT('enroll').'</a>)</small>';
						}
					}
				}else{
					echo '<td align="center">'._AT('no').'<small> (<a href="'.$enroll_manager.'?id0='.$row['member_id'].SEP.'func=enroll'.SEP.'tab=0'.SEP.'course_id='.$course_id.'">'._AT('enroll').'</a>)</small></td>';
				}
			} else {
				echo '<td align="center">'._AT('no').'</td>';
			}
		}
		echo '</tr></table>';
} else {
	$msg->printInfos('EC_NO_STUDENTS_ENROLLED');
}

require (AT_INCLUDE_PATH.'footer.inc.php'); ?>