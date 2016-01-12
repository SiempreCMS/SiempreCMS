<?php
/*
 * This file is part of Siempre CMS
 *
 * (c) 2015 Steve Morgan http://siempresolutions.co.uk/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//  Purpose:-      The main user class, handles passwords, user profile etc

// TO DO
// 1) I can't decide if the user profile check should be a function and return a problems[] array - might change this later

class user {

	// definitions
	private static $db;    
	public $results;
	public $userID;
   
	function __construct() 
	{
		// Init variables
		self::$db = DBCxn::Get();	
	}
  
  
  	function getUser($userID) 
	{
  	// Gets the user details from the DB
 		$this->userID = $userID;
		
  		try {
			$db =  self::$db;
			
			$sql = "SELECT `ID`, `username`, `foreName`, `lastName`, `email`, '2010-01-01' AS `lastLogin`, '2010-01-01' AS `lastUpdated`, '2010-01-01' AS `created` 
					FROM cms_user 
					WHERE ID = :userID 
					LIMIT 1;";
			//$query = self::$db->prepare($sql);
			$query = $db->prepare($sql);
			$query->bindParam(':userID', $this->userID);
			//print $sql;
			$query->execute();
		
			// If we have a row
			if($query->rowCount() != 0) {
				$result = $query->fetch();
				$this->results['userID'] = $result['ID'];
				$this->results['userName'] = $result['username'];
				$this->results['foreName'] = $result['foreName'];
				$this->results['lastName'] = $result['lastName'];
				$this->results['email'] = $result['email'];
				$this->results['lastUpdated'] = $result['lastUpdated'];
				$this->results['created'] = $result['created']; 
				$this->results['lastLogin'] = $result['lastLogin'];				
				
			} else {
				// thrown an exception
				throw new Exception('User not in the database'); 
			}
			//error_log('User ID - ' . $_SESSION['customerID']);
		} catch (Exception $e) {
			error_log('Throwing exception in the getUser');
			error_log('User ID - ' . $this->userID . ' has thrown exception in the get customer details ' . $e->getMessage());
			return false;
		}
		return true;
	}

	
	function updateUser($userID, $foreName, $lastName, $email)
	{
		// Updates the customer details
		// INPUT MUST BE validated in the AJAX / Web Service
		// avoid attempts to change web IDs
		try { 
			if ($userID > 0 ) {
				$db =  DBCxn::Get();
				
				$userID = trim($userID);
				$foreName = trim($foreName);
				$lastName = trim($lastName);
				$email = trim($email);
				
				$query = $db->prepare("UPDATE cms_user 
											SET foreName = :foreName, lastName =:lastName,
												email = :email, lastUpdated = NOW()  
											WHERE ID = :userID;");
				$query->bindParam(':userID', $userID, PDO::PARAM_INT);							
				$query->bindParam(':foreName', $foreName, PDO::PARAM_STR, 100);
				$query->bindParam(':lastName', $lastName, PDO::PARAM_STR, 100);
				$query->bindParam(':email', $email, PDO::PARAM_STR, 100);
				
				$query->execute();
			}
			else {
				return false;
			}
		}
		catch (PDOException $e)
		{
				error_log("Updating User failed.\n");
				error_log("getCode: ". $e->getCode() . "\n");
				error_log("getMessage: ". $e->getMessage() . "\n");
				return false;
			}
			
			return true;	
		}

	function updatePassword($userID, $newPassword) 
  	{
		$db =  DBCxn::Get();
		// Sets the users password from the DB			
		try 
		{
			$newPassword = sha1(SALT.trim($newPassword));
			$userID = trim($userID);
			$query = $db->prepare("UPDATE cms_user SET passwordhash = :password WHERE ID = :userID;"); 

			$query->bindParam(':userID', $userID);
			$query->bindParam(':password', $newPassword); 
			
			$query->execute();
		}
		catch (PDOException $e)
		{
			error_log("Updating Password failed.\n");
			error_log("getCode: ". $e->getCode () . "\n");
			error_log("getMessage: ". $e->getMessage () . "\n");
			return false;
		}

		return true;
	}
	
 
	function validatePassword($password)
	{
		// Function currently just used in the editProfile - should be used in the Join also
		// Password is correct length and meets the mask?
		if (strlen(htmlentities($password)) >= 5 && strlen(htmlentities($password)) <= 50) {
			if (preg_match('/^[a-zA-Z0-9_!?@#$%]{5,50}$/',$password)) {
				return true;
			}
			else  {
				return false;
			}
		}
		return false;
	}
 	
	function isValidEmailAddress($email)
	{
		// return (preg_match("/^(\w+((-\w+)|(\w.\w+))*)\@(\w+((\.|-)\w+)*\.\w+$)/",$email));
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return false;
		} else {
			return true;
		}
	}
	
	function setUserName($userName)
	{
		// Passed the user entered user_name, cleans and checks
		$userName = trim($userName);	
		
		if (! $this->isUserNameLenValid($userName)) {
			$this->problems = $this->problems . 'Please enter a username between 3 - 15 characters long <br/>';
			//$userNameProb = True;
			return False;
		}
		else {
			// Check it is valid and free
			if (! $this->isUserNameValid($userName)) {
				$this->problems = $this->problems . 'Username name invalid! Only user letters, numbers and underscores. <br/>';
				//$userNameProb = True;
				return False;
			}
			else {
				if (! $this->isUserNameFree($userName)) {
					$this->problems = $this->problems . 'Username name taken! Please try another. <br/>';
					//$userNameProb = True;
					return False;
				}
				else {
					//$userNameValid = True;  // TO DO - WILL I USE THIS?
					$this->userName = $userName;
					return True;
				}
			}
		}
	}
	
	function isUserNameLenValid($userName)
	{
		// Checks the len of the user_name_free
		if ((strlen($userName) >= 3) && (strlen($userName) <= 15 )) {
			return true;
		}
		else {
			return false;
		}
	}
	
	
	function isUserNameValid($userName) 
	{
		// Checks username only contains A-Z, a-Z, 0-9 and / or underscore
		
		if(preg_match('/^[A-Za-z0-9_]*$/',$userName)) {
			return true;
			}
		else {
			return false;
			}
	}
	
	
	function isUserNameFree($userName) 
	{
		$userName =  strtolower(trim($userName));
		
		// Checks to see if the user name is used already
		$db = DBCxn::Get();
		$query = $db->prepare("SELECT ID FROM cms_user WHERE userName = :userName;");
		$query->bindParam(':userName', $userName);
		$query->execute();
	   // If we didn't find any rows
		if($query->rowCount() == 0) {
			return true;
		}
	   else {
			// Username exists
			return false;
	   }
	}
	
	function newUser($userName, $foreName, $lastName, $email)
	{
		//
		$db =  DBCxn::Get();
		
		try 
		{
			// Generate a GUID for email validation
			$GUID = strtolower(sha1(SALT.trim(uniqid())));
			$email = strtolower(trim($email));
			$userName =  trim($userName);
			$foreName =  trim($foreName);
			$lastName =  trim($lastName);
			
			$query = $db->prepare("INSERT INTO cms_user (username, active, foreName, lastName, email, passwordhash, created, lastUpdated) 
				VALUES (:userName, 1, :foreName, :lastName, :email, UUID(), NOW(), NOW());");
			$query->bindParam(':userName', $userName);
			$query->bindParam(':foreName', $foreName); 
			$query->bindParam(':lastName', $lastName);
			$query->bindParam(':email', $email); 			
			$query->execute();
			
			$this->userID = $db->lastInsertId();
						
			$query = null;
		}
	   catch (PDOException $e)
   		{
     		error_log('Error Creating User'. $e->getMessage());
			$this->problems .= "The statement failed.\n";
     		$this->problems .=  "getCode: ". $e->getCode () . "\n";
     		$this->problems .=  "getMessage: ". $e->getMessage () . "\n";
   		}
		return true;
	}			  	
} 
?>
