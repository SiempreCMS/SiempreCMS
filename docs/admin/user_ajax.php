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
require_once('../../admin-includes/admin.security_ajax.inc.php');

$user = new user();

if (isset($_POST['action'])) {

	////////////////////////////////////////////////////
	// loadUser e.g. LOAD USER FROM ID 
	////////////////////////////////////////////////////		
	if ($_POST['action'] == 'loadUser' ) {
		if(isset($_POST['userID'])&&is_int((int)$_POST['userID'])) {		
			$foundRecord = $user->getUser((int)$_POST['userID']);

			echo(json_encode(array('result' => $foundRecord, 'results' => $user->results)));
			exit();
		}
		else {
			echo(json_encode(array('result' => 'false')));
			exit();
		}
	}

	
	////////////////////////////////////////////////////
	// SET - e.g. UPDATE USER
	////////////////////////////////////////////////////
	if ($_POST['action'] == 'set' ) {

		if(isset($_POST['user-id'])&&
			isset($_POST['user-forename'])&&
			isset($_POST['user-lastname'])&&
			isset($_POST['user-email'])) 
		{			
			// Validate  and sanitise
			$userID = (int)$_POST['user-id'];
			$foreName = $_POST['user-forename'];
			$lastName = $_POST['user-lastname'];
			$email = $_POST['user-email'];
			
			// validate email
			if(!$user->isValidEmailAddress($email))
			{
				echo(json_encode(array('result' => false, 'msg' => 'Invalid email address')));
				exit();
			}

			// OK update the user
			$result = $user->updateUser($userID, $foreName, $lastName, $email);
			
			echo(json_encode(array('result' => true)));
			exit();
		}
		else {
			echo(json_encode(array('result' => false)));
			exit();
		}
	}
	
	
	////////////////////////////////////////////////////
	// UPDATE PASSWORD 
	////////////////////////////////////////////////////
	if ($_POST['action'] == 'setPassword' &&
		isset($_POST['user-id'])&&
		isset($_POST['user-password1'])&&
		isset($_POST['user-password2'])) 
	{			
		// Validate  and sanitise
		$userID = (int)$_POST['user-id'];
		$password1 = trim(htmlentities($_POST['user-password1']));
		$password2 = trim(htmlentities($_POST['user-password2']));
		
		if (DEMO) 
		{
			echo(json_encode(array('result' => false, 'msg' => 'Sorry - you can\'t change passwords in the demo system, thanks!')));
			exit();
		}
		
		// TO DO - change to admin role
		if ($_SESSION['userID'] != 1000 && $_POST['user-id'] != $_SESSION['userID']) {
			echo(json_encode(array('result' => false, 'msg' => 'Sorry - you can\'t change passwords for other users, thanks!')));
			exit();
		}		
		
		if ($password1 !== $password2) 
		{
			echo(json_encode(array('result' => false, 'msg' => 'Passwords do not match')));
			exit();
		}
		if (!$user->validatePassword($password1))
		{
			echo(json_encode(array('result' => false, 'msg' => 'Password must be between 5 - 15 characters long and contain only letters, numbers or underscores')));
			exit();
		}

		// OK update the user password
		$result = $user->updatePassword($userID, $password1);
		echo(json_encode(array('result' => $result)));
		exit();	
	}
	
	
	////////////////////////////////////////////////////
	// NEW - e.g. CREATE USER
	////////////////////////////////////////////////////
	if ($_POST['action'] == 'new' && 
		isset($_POST['user-username'])&&
		isset($_POST['user-forename'])&&
		isset($_POST['user-lastname'])&&
		isset($_POST['user-email'])) {	
			// Validate  and sanitise
			// TO DO Validate and sanitise Can't validate the customer ID here (security risk) - will be done in the
			$userName = $_POST['user-username'];
			$foreName = $_POST['user-forename'];
			$lastName = $_POST['user-lastname'];
			$email = $_POST['user-email'];
			
			if(!$user->isUserNameFree($userName))
			{
				echo(json_encode(array('result' => false, 'msg' => "Username is not unique - username already in use")));
				exit();
			}
			if(!$user->isValidEmailAddress($email))
			{
				echo(json_encode(array('result' => false, 'msg' => "Email is not valid")));
				exit();
			}
			
			
			$result = $user->newUser($userName, $foreName, $lastName, $email);

			echo(json_encode(array('result' => true, 'userid' => $user->userID)));
			exit();
	}
	//	else {
	//		echo(json_encode(array('result' => false)));
	//		exit();
	//	}
//	}
}	
	echo(json_encode(array('result' => false, 'msg' => 'AJAX Validation failed')));
	exit();

?>