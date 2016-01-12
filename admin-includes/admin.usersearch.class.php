<?php
/*
 * This file is part of Siempre CMS
 *
 * (c) 2015 Steve Morgan http://siempresolutions.co.uk/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class usersearch {

	//public definitions
	private static $db;   
	// More
	public $results;
	public $totalRows;
   
	function __construct() 
	{
		// Init variables
		self::$db = DBCxn::Get();	
	}
  
  
  	function getUsers($name, $userName, $perPage, $pageNumber) 
  	{
		// Avoid stupid values 
		if ($perPage > 100) {
			$perPage = 100;
		}
		else if ($perPage <= 0) {
			$perPage = 20;
		}
		
		
  		// Gets the customer results from the DB
  		try {
			$db =  self::$db;
			
			$sql = "SELECT SQL_CALC_FOUND_ROWS `ID`, `username`, `foreName`, `lastName`, `email`, `lastLogin`, `lastUpdated`, `created` 
					FROM cms_user 
					WHERE 1 = 1 AND ID > 0 ";
			// WHERE STATEMENTS
			if ($name !=='')
				$sql .= "AND lastName LIKE :name OR foreName LIKE :name ";	
			if ($userName !=='')
				$sql .= "AND userName LIKE :userName ";	
			
			// Paging
			$sql .= "LIMIT :perPage 
					OFFSET :pageNumber;";
					
			$query = $db->prepare($sql);
		
			// Now bind - adding wildcards if necess
			if ($name !=='') {
				$name = '%'.$name.'%';
				$query->bindParam(':name', $name);
			}
			if ($userName !=='') {
				$userName = '%'.$userName.'%';
				$query->bindParam(':userName', $userName);
			}
			$query->bindParam(':perPage', $perPage, PDO::PARAM_INT);
			$query->bindParam(':pageNumber', $pageNumber, PDO::PARAM_INT);
			

			$query->execute();
			// If we have a row(s)
			if($query->rowCount() != 0) {
				foreach ($query as $key => $result) {
					$this->results[$key]['userID'] = $result['ID'];
					$this->results[$key]['userName'] = $result['username'];
					$this->results[$key]['foreName'] = $result['foreName'];
					$this->results[$key]['lastName'] = $result['lastName'];
					$this->results[$key]['email'] = $result['email'];
					$this->results[$key]['lastLogin'] = $result['lastLogin'];
					$this->results[$key]['created'] = $result['created']; 
					$this->results[$key]['lastUpdated'] = $result['lastUpdated']; 
				}		
			} 
			else {
				// thrown an exception
				throw new Exception('No Users in the database'); 
			}

			// Now get the total number of rows we would have got without a LIMIT to provide the paging controls
			// Only works due to the SQL_CALC_FOUND_ROWS  in the SELECT
			$sql = "SELECT FOUND_ROWS();";
			$query = $db->prepare($sql);
			$query->execute();
			// If we have a row
			if($query->rowCount() != 0) {
				$result = $query->fetch();
				$this->totalRows = $result['FOUND_ROWS()'];
			}
		} catch (Exception $e) {
			error_log('Throwing exception in the get Users search');
			error_log('has thrown exception in the get Users  search ' . $e->getMessage());
			return false;
		}
		return true;
	}
 } 
?>
