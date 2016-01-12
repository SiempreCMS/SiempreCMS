<?php
/*
 * This file is part of Siempre CMS
 *
 * (c) 2015 Steve Morgan http://siempresolutions.co.uk/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
//  Purpose:-      The main user login class
//  Based on http://hvassing.com/2007/simple-php-login-script-using-session-and-mysql/

require_once('../../admin-includes/admin.base.inc.php');


class login {

	//public definitions
	public $result;  
	public static $db;
   
	function __construct() {
		//echo 'login Constructor called';
		self::$db = DBCxn::Get();
	}
  
  
	function checkIP($IP){
	
		// Randomly check to clear the spamCheck table
		$rand = rand(1, 50);  // 7 is a hit
		if ($rand == 7) {
			$query = self::$db->prepare("DELETE FROM cms_login_ip WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 60 MINUTE);");
			$query->execute();
			$query = null;
		}
	
		try {
			$query = null;
			$query = self::$db->prepare("INSERT INTO cms_login_ip (ip) VALUES (:ip);");
			$query->bindParam(':ip', $IP);
			
			$query->execute();
			$query = null;

			try {
				$query = self::$db->prepare("SELECT ID FROM cms_login_ip WHERE ip = :ip AND attempt_time > (NOW() - INTERVAL :lockout MINUTE);");
				$query->bindParam(':ip', $IP);
				$query->bindValue(':lockout', PDO::PARAM_INT, LOCKOUTMINS);

				$query->execute();

				if($query->rowCount() > MAXIPATTEMPTS) {
					// Don't let user log in...
					error_log('HIT IP limit for log ins '.$IP);
					return false;
				}
				else {
					return true;
				}
			}
			catch (Exception $e){
				error_log('Error counting IP attempts' . $e->getMessage());
				return false;
			}	
		}
		catch (Exception $e) {
				error_log('Error inserting IP attempt' . $e->getMessage());
				return false;
		}
				
		// shouldn't get here
		return false;

	}
	
	
	function processLogin($username, $password) {
		// Takes the username and login and checks the password			
		// error_log("User Login Attempt" .  $_POST['username'] . " - " . $_POST['password']);
		error_log("User Login Attempt: " .  $_POST['username']);
		
		// First sanitise user input and create sha1 hash of pword
		$username = htmlentities($_POST['username'], ENT_QUOTES, 'UTF-8');   
		$password = sha1(SALT.htmlentities($_POST['password'], ENT_QUOTES, 'UTF-8'));
	  
		// Avoid risk of sql injection
		$query = self::$db->prepare("SELECT ID  
										FROM cms_user 
										WHERE (username = :username AND passwordhash = :password)
										LIMIT 1;");
		$query->bindParam(':username', $username);
		$query->bindParam(':password', $password);  

		$query->execute();

		if($query->rowCount() < 1) {
			// MySQL returned zero rows (or there's something wrong with the query)
			return false;
		}
   		else 
		{
			// User validated
			$row = $query->fetch();
			
			// Register the user ID for further use
			$userID = $row['ID'];
			$_SESSION['userID'] = $userID;
			$_SESSION['username'] = $username;
			$_SESSION['LAST_ACTIVITY'] = time();
			
			error_log('User ID - ' . $userID . ' logged in');
			
			// Set the last logged in
			$this->updateLastLogin($userID);
			return true;
		}
		// shouldn't get here
		return false;
	}
	
	
/*	function processForgotLogin() {
		// Takes the email address, creates a guid in the table and emails this to the user
		
		try {
			// First sanitise user input and create sha1 hash of pword
			$email = htmlentities($_POST['email'], ENT_QUOTES, 'UTF-8');   
			$GUID = strtolower(sha1(SALT.trim(uniqid())));
		  
			// Avoid risk of sql injection
			//$query = self::$db->prepare("SELECT ID FROM members WHERE username = :username AND user_password = :password LIMIT 1;");
			$query = self::$db->prepare("SELECT ID, foreName, userName FROM user WHERE email = :email ORDER BY ID LIMIT 1;");
			$query->bindParam(':email', $email);

			$query->execute();

			if($query->rowCount() == 0) {
				// MySQL returned zero rows (or there's something wrong with the query)
				// DO NOTHING - we don't want to show that the email is invalid
				error_log('I am not inserting');
				return false;
			}
			else 
			{
				try {
					// User validated
					$row = $query->fetch();
					$userID = $row['ID'];
					$foreName = $row['foreName'];
					$userName = $row['userName'];
					
					$query = null;
					$query = self::$db->prepare("INSERT INTO userpasswordreset (userID, GUID) VALUES (:userID, :GUID);");
					$query->bindParam(':userID', $userID);
					$query->bindParam(':GUID', $GUID);
					
					$query->execute();
					$query = null;
					$sendEmail = new email();
				
					$sendEmail->sendForgotPasswordEmail($userID, $userName, $foreName, $GUID, $email);
				} 
				catch (Exception $e) {
					error_log('Error inserting password code' . $e->getMessage());
					return false;
				}
			}
					return true;
		} 
		catch (Exception $e) {
			error_log('Error inserting password code' . $e->getMessage());
			return false;
		}
		
		return true;
		
	}
	
	
	function resetPassword($userID, $GUID, $newPassword)
	{
		// Set the users password		
		// check the GUID and ID
		// IF ID and GUID exist
		try {
			$newPassword = sha1(SALT.htmlentities($newPassword, ENT_QUOTES, 'UTF-8'));
			
			$query = self::$db->prepare('SELECT userID FROM userpasswordreset WHERE userID = :userID AND GUID = :GUID;');
			$query->bindParam(':userID', $userID);
			$query->bindParam(':GUID', $GUID);

			$query->execute();

			if($query->rowCount() > 0) {
				$query = null;
				$query = self::$db->prepare('UPDATE user
												SET password = :newPassword WHERE ID = :userID;');
				$query->bindParam(':newPassword', $newPassword);
				$query->bindParam(':userID', $userID);
				$query->execute();
			}
			else 
			{
				return false;
			}
		}
		catch (Exception $e) {
			error_log('Error inserting password code' . $e->getMessage());
			return false;
		}
				
		return true;
	}
	
	
	function validatePassword($password)
	{
		// OO is rubbish  as i've repeated this 
		// Password is correct length and meets the mask?
		if (!(strlen(htmlentities($password)) >= 5 && strlen(htmlentities($password)) <= 15)) {
			return false;
		}
		else {
			return true;
		}
	}
	*/
	function updateLastLogin($userID)
	{
		$query = self::$db->prepare("UPDATE cms_user SET lastLogin = NOW() WHERE ID = :userID;");
		$query->bindParam(':userID', $userID);  

		$query->execute();
	}
} 
?>
