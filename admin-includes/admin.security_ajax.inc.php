<?php
/*
 * This file is part of Siempre CMS
 *
 * (c) 2015 Steve Morgan http://siempresolutions.co.uk/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

session_start();

require_once('admin.config.inc.php');


if (!isset($_SESSION['userID'])) {	
	error_log("Ajax sec not logged in");
	echo(json_encode(array('NotLoggedIn' => true, 'msg' => 'Not logged in')));
	exit();
}
else {
	
	if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > (LOGOUTMINS * 60))) {
		// last request was more than 30 minutes ago
		session_unset();     // unset $_SESSION variable for the run-time 
		session_destroy();   // destroy session data in storage
		echo(json_encode(array('NotLoggedIn' => true, 'msg' => 'User ID error')));
		exit();
	}
	$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
	
	$userID = intval($_SESSION['userID']);

	if ($userID < 1) {
		error_log("Weird user ID detected - $userID");
		echo(json_encode(array('NotLoggedIn' => true, 'msg' => 'User ID error')));
		exit();
	}
} 
?>