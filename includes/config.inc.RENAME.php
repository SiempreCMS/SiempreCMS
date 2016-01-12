<?php
/*
 * This file is part of Siempre CMS
 *
 * (c) 2015 Steve Morgan http://siempresolutions.co.uk/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
	// Domain - enter with http:// suffix and NO trailing '/' 
	define('DOMAIN', 'http://www.mydomain.com');
	define('EMAIL', 'myemail@here.com');
	
	
	// Template settings
	define('STARTTOKEN', '{|');
	define('ENDTOKEN', '|}');
	define('DEBUG', true);
	
	// DB Settings
	// localhost is SLOW - use 127.0.0.1
	define('DB_SERVER', '127.0.0.1');
	define('DB_NAME', 'siempre_cms');
	define('DB_USER', 'myDBuser');
	define('DB_PASSWORD', 'myDBpassword');
	
	// Cache settings
	define('CACHE_ENABLED', false);
	define('CACHE_LENGTH', 300); // cache length in seconds
	
?>