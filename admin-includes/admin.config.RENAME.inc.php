<?php
/*
 * This file is part of Siempre CMS
 *
 * (c) 2015 Steve Morgan http://siempresolutions.co.uk/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
// Define a salt for password hashes
define('SALT', 'dav1db0w1esblueshoes');
  
// Stops brute force log in attempts - well makes it harder!
define('MAXIPATTEMPTS', 5);
define('LOCKOUTMINS', 30);
define('LOGOUTMINS', 60);

// Used on the process login to stop user trying to redirect to an admin page.  
$redirectWhiteList = array('content.php', 'templates.php', 'user.php');
define("REDIRECTWHITELIST", serialize($redirectWhiteList));

// presently only used for the login page notes  DO NOT USE IN PRODUCTION!!
define('DEMO', false);  


// DB Settings
// localhost is SLOW - use 127.0.0.1
define('DB_SERVER', '127.0.0.1');
define('DB_NAME', 'siempre_cms');
define('DB_USER', 'myDBuser');
define('DB_PASSWORD', 'myDBpassword');

?>
