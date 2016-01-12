<?php
/*
 * This file is part of Siempre CMS
 *
 * (c) 2015 Steve Morgan http://siempresolutions.co.uk/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// Sends the user to the login-page if not logged in unless the page is a safe page
session_start();
//session_regenerate_id(true);

// if the session is not set and we're not on a safe page
$page = basename($_SERVER["REQUEST_URI"]);
if(!isset($_SESSION['userID']) && !in_array($page, $safePages)) {
	// I'm destroying the session here... to fix validation on join (?) I'm not sure I should. 
	session_destroy();
	header('Location: login.php?msg=requires_login&loc='.urlencode(basename($_SERVER['REQUEST_URI'])));
	exit();  // exit so no further code is run
}
else {
	
	if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > (LOGOUTMINS * 60))) {
		// last request was more than 30 minutes ago
		session_unset();     // unset $_SESSION variable for the run-time 
		session_destroy();   // destroy session data in storage
		header('Location: login.php?msg=requires_login&loc='.urlencode(basename($_SERVER['REQUEST_URI'])));
		exit();  // exit so no further code is run
	}
	$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
	
	$username =  $_SESSION['username'];
	$userID = $_SESSION['userID'];
}
?>
