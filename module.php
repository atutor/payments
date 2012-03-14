<?php
/*******
 * doesn't allow this file to be loaded with a browser.
 */
if (!defined('AT_INCLUDE_PATH')) { exit; }

/******
 * this file must only be included within a Module obj
 */
if (!isset($this) || (isset($this) && (strtolower(get_class($this)) != 'module'))) { exit(__FILE__ . ' is not a Module'); }

/*******
 * assign the instructor and admin privileges to the constants.
 */
define('AT_PRIV_ECOMM',       $this->getPrivilege());
define('AT_ADMIN_PRIV_ECOMM', $this->getAdminPrivilege());



/*******
 * instructor Manage section:
 */
$this->_pages['mods/payments/response_ipn.php']['title_var']     = 'ec_payments';
$this->_pages['mods/payments/response_user.php']['title_var']    = 'ec_payments';
$this->_pages['mods/payments/error_beanstream.php']['title_var']    = 'ec_payments';
$this->_pages['mods/payments/success_beanstream.php']['title_var']    = 'ec_payments';
$this->_pages['mods/payments/failure_beanstream.php']['title_var']    = 'ec_payments';
$this->_pages['mods/payments/success_monerisusa.php']['title_var']    = 'ec_payments';
$this->_pages['mods/payments/error_monerisca.php']['title_var']    = 'ec_payments';
$this->_pages['mods/payments/success_monerisca.php']['title_var']    = 'ec_payments';
$this->_pages['mods/payments/failure_monerisca.php']['title_var']    = 'ec_payments';

$this->_pages['mods/payments/index_instructor.php']['title_var'] = 'ec_payments';
$this->_pages['mods/payments/index_instructor.php']['parent']    = 'tools/index.php';
$this->_pages['mods/payments/index_instructor.php']['children']  = array('mods/_core/enrolment/index.php');
$this->_pages['tools/enrollment/index.php']['children']       = array('mods/payments/index_instructor.php');

/*******
 * add the admin pages when needed.
 */
if (admin_authenticate(AT_ADMIN_PRIV_ECOMM, TRUE) || admin_authenticate(AT_ADMIN_PRIV_ADMIN, TRUE)) {
	$this->_pages[AT_NAV_ADMIN] = array('mods/payments/payments_admin.php');
	
	$this->_pages['mods/payments/payments_admin.php']['title_var'] = 'ec_payments';
	
	$this->_pages['mods/payments/payments_admin.php']['parent']    = AT_NAV_ADMIN;

	$this->_pages['mods/payments/payments_admin.php']['children'] = array('mods/payments/index_admin.php','mods/payments/index_admin_approve.php', 'mods/payments/index_instructor.php','mods/_core/enrolment/admin/index.php', );
	
	$this->_pages['mods/payments/index_instructor.php']['title_var'] = 'ec_payments_courses';	
	
	$this->_pages['mods/payments/index_instructor.php']['children']  = array('mods/_core/enrolment/admin/index.php');
	
	$this->_pages['mods/payments/index_instructor.php']['parent']    = 'mods/payments/payments_admin.php';
	
	$this->_pages['mods/payments/index_admin.php']['title_var'] = 'ec_settings';
	
	$this->_pages['mods/payments/index_admin.php']['parent']    = 'mods/payments/payments_admin.php';

	$this->_pages['mods/_core/enrolment/admin/index.php']['children']       = array('mods/payments/index_instructor.php');

	$this->_pages['mods/payments/index_admin_approve.php']['title_var'] = 'ec_approve_manually';
		
	$this->_pages['mods/payments/index_admin_approve.php']['parent']    = 'mods/payments/payments_admin.php';
}

/* my start page pages */
$this->_pages[AT_NAV_START]  = array('mods/payments/index.php');
$this->_pages['mods/payments/index.php']['title_var'] = 'ec_payments';
$this->_pages['mods/payments/index.php']['parent']    = AT_NAV_START;

$this->_pages['mods/payments/payment.php']['title_var'] = 'ec_payments';
$this->_pages['mods/payments/payment.php']['parent']    = 'mods/payments/index.php';
$this->_pages['mods/payments/index.php']['children']    = array('users/index.php','users/browse.php');

$this->_pages['mods/payments/failure.php']['title_var'] = 'ec_payments';
$this->_pages['mods/payments/invoice.php']['title_var'] = 'ec_payments';
?>