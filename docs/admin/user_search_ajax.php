<?php
/*
 * This file is part of Siempre CMS
 *
 * (c) 2015 Steve Morgan http://siempresolutions.co.uk/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
// includes and security
require_once('../../admin-includes/admin.base.inc.php');
require_once('../../admin-includes/admin.security_ajax.inc.php');

if (isset($_POST['action']) && $_POST['action'] == 'search' )
	{	
	$name = trim(htmlentities($_POST['user-search-name']));
	$userName = trim(htmlentities($_POST['user-search-username']));
	$perPage = intval(trim(htmlentities($_POST['user-search-perpage'])));
	$offset = (intval(trim(htmlentities($_POST['user-search-page']))) - 1) * $perPage;
	
	$usersearch = new usersearch();
	if($usersearch->getUsers($name, $userName, $perPage, $offset)) {	
		echo(json_encode(array('result' => true, 'results' => $usersearch->results, 'totalrows' => $usersearch->totalRows)));
		exit();
	}
	else {
		echo(json_encode(array('result' => false, 'results' => null)));
		exit();
	}
}
else {
	echo(json_encode(array('result' => false, 'results' => null)));
	exit();
}

?>