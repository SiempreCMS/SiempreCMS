<?php
/*
 * This file is part of Siempre CMS
 *
 * (c) 2015 Steve Morgan http://siempresolutions.co.uk/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
require_once('../../admin-includes/admin.base.inc.php');
session_start();
session_regenerate_id(true);
$login = new login();


if (isset($_POST['action']) && $_POST['action']=='login' && isset($_POST['username']) && isset($_POST['password'])) {

	$usersIP = getenv("REMOTE_ADDR");
	////////////////////////////////////////////////////
	// check user's IP is not slamming the site then login
	////////////////////////////////////////////////////		
	if ($login->checkIP($usersIP)===true) {
		$username = trim($_POST['username']);
		$password = trim($_POST['password']);
		
		if($login->processLogin($username, $password))	{	
			$_SESSION['justLoggedIn'] = true;
			echo(json_encode(array('msg' => 'Yup', 'result' => true)));
			exit();
		}
		else {
			echo(json_encode(array('msg' => 'Incorrect_login', 'result' => false)));
			exit();
		}
	}
	else 
	{
		// someone is trying a brute force.  allowed 10 attempts then we ban them for 30 minutes
		echo(json_encode(array('msg' => 'IP_locked' , 'result' => false)));
		exit();
	}	
}

if (isset($_POST['action']) && $_POST['action']=='login' && isset($_POST['userID']) && isset($_POST['password'])) {

	////////////////////////////////////////////////////
	// FORGOT PASSWORD
	////////////////////////////////////////////////////		
	
	// TO DO 
	echo(json_encode(array('msg' => 'Check your email for your temporary password', 'result' => true)));
}

// TO DO 
// link back for forgotten password reset

exit();
?>