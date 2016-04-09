<?php
/*
 * This file is part of Siempre CMS
 *
 * (c) 2016 Steve Morgan http://siempresolutions.co.uk/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

	//  Purpose:-  Gets the version number from DB and performs updates

class vercheck {
	
	private static $db;  
	public $message;	
	
	function __construct() 
	{
		// Init variables
		self::$db = DBCxn::Get();	
	}
	
	function getDBVersion() 
	{
		try 
		{		
			// 1. get version
			$sql = "SELECT version 
						FROM `cms_version`
						LIMIT 1					
							;";
			
			$query = self::$db->prepare($sql);
			$query->execute();
			
			// If we have a row
			if($query->rowCount() != 0) {
				$result = $query->fetch();
				$version = $result['version'];
			} else {
				$version = "0";
			}
		} 
		catch (PDOException $e)
		{
			error_log("Error obtaining Version from DB.\n");
			error_log("getCode: ". $e->getCode() . "\n");
			error_log("getMessage: ". $e->getMessage() . "\n");
			return "0";
		}
		catch (Exception $e)
		{
			error_log("Error obtaining Version from DB - NOT PDO.\n");
			return "0";
		}
		
		return $version;
	}
	
	function performUpgrade()
	{
		$dbVersion = $this->getDBVersion();
		
		try 
		{
			if($dbVersion == "0")
			{
				error_log("VERSION 1.2 - PERFORMING DB UPGRADE TO 1.3.2");
				
				// create version table
				$sql = "CREATE TABLE IF NOT EXISTS `cms_version` (
					  `version` varchar(10) NOT NULL
					) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

				$query = self::$db->prepare($sql);
				$query->execute();
				
				// data for version table
				$sql = "INSERT INTO `cms_version` (`version`) VALUES ('1.2');";
				$query = self::$db->prepare($sql);
				$query->execute();
			}
			
			if($dbVersion < "1.3.2")
			{
				error_log("PERFORMING DB UPGRADE TO 1.3.2");
				$sql = "ALTER TABLE `cms_content` ADD `noCache` BOOLEAN NOT NULL DEFAULT FALSE ;";
				$query = self::$db->prepare($sql);
				$query->execute();
				
				$sql = "ALTER TABLE `cms_content` ADD `parentIDs` VARCHAR(500) NOT NULL;";
				$query = self::$db->prepare($sql);
				$query->execute();
				
				//-- clean up the evil orphaned page paths
			//	$sql = "delete from cms_page_path where id in(SELECT ID from cms_page_path where nodeID NOT IN (Select id from cms_tree_data));";
			//	$query = self::$db->prepare($sql);
			//	$query->execute();

				// update version table
				$sql = "UPDATE `cms_version` SET `version`= '1.3.2';";
				$query = self::$db->prepare($sql);
				$query->execute();
			}
			
			if($dbVersion < "1.3.3")
			{
				error_log("PERFORMING DB UPGRADE TO 1.3.3");
				// update version table
				$sql = "UPDATE `cms_version` SET `version`= '1.3.3';";
				$query = self::$db->prepare($sql);
				$query->execute();
			}
			
			if($dbVersion < "1.3.4")
			{
				error_log("PERFORMING DB UPGRADE TO 1.3.4");
				// update version table
				$sql = "UPDATE `cms_version` SET `version`= '1.3.4';";
				$query = self::$db->prepare($sql);
				$query->execute();
			}
			
			if($dbVersion < "1.3.5")
			{
				error_log("PERFORMING DB UPGRADE TO 1.3.5");
				// update version table
				$sql = "UPDATE `cms_version` SET `version`= '1.3.5';";
				$query = self::$db->prepare($sql);
				$query->execute();
			}		
		} 
		catch (PDOException $e)
		{
			error_log("Error obtaining Version from DB.\n");
			error_log("getCode: ". $e->getCode() . "\n");
			error_log("getMessage: ". $e->getMessage() . "\n");
			$this->message = "getCode: ". $e->getCode() . "getMessage: ". $e->getMessage() . "\n";
			return "0";
		}
		catch (Exception $e)
		{
			$this->message .= " - error found";
			error_log("Error obtaining Version from DB - NOT PDO.\n");
			return "0";
		}
		return true;
	}
	
}
	
?>