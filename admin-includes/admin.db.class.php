<?php
/*
 * This file is part of Siempre CMS
 *
 * (c) 2015 Steve Morgan http://siempresolutions.co.uk/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class DBCxn {
	public static $driverOpts = null;
	
	// Internal variable to hold the connection
	private static $db;
	// No cloning or instantiating allowed
	final private function __construct() {  }
	final private function __clone() {  }
	
	public static function get() {
		// Connect if not already connected
		if (is_null(self::$db)) {
			try {
				// sprintf to get around the fact you can't create the dsn dynamically by concatting vars. 
				self::$db = new PDO(sprintf("mysql:host=%s;dbname=%s;charset=utf8", DB_SERVER, DB_NAME ), DB_USER, DB_PASSWORD, self::$driverOpts);
				
				// set up PDO to throw exceptions so I can use try and catch blocks in the code http://www.kitebird.com/articles/php-pdo.html#TOC_5
				self::$db->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			}
			catch(PDOException $e) {
				echo $e->getMessage();
			}
		}

		// Return the connection
		return self::$db;
	}
}
?>
