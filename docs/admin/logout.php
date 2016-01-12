<?php
/*
 * This file is part of Siempre CMS
 *
 * (c) 2015 Steve Morgan http://siempresolutions.co.uk/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
session_start();  
session_regenerate_id(true);

// Can't remember where I got this code from but it seemed to suggest that unless you use the unregister func
// you need a wait-  this func is now deprecated!
//if(true === session_unregister('userID')) :
//if(true === unset($_SESSION['userID'])) :

   unset($_SESSION['userID']);
   sleep(2);
   header('Location: login.php?msg=logout_complete');

?> 
