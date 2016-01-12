<?php
/*
 * This file is part of Siempre CMS
 *
 * (c) 2015 Steve Morgan http://siempresolutions.co.uk/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

	// Base include for Siempre CMS Admin backend
  
	// Config
	require_once('admin.config.inc.php');
	
	// Functions
	require_once('standard.funcs.php');  
	
	// DB / DAO
	require_once('admin.db.class.php');
  
	// Classes
	require_once('admin.login.class.php');
	require_once('admin.content.class.php');
	require_once('admin.template.class.php');
	require_once('admin.cache.class.php');
	require_once('admin.user.class.php');
	require_once('admin.usersearch.class.php');
	require_once('admin.vercheck.class.php');

	// For JS Tree
	// TO DO merge the DB connection so there is only one in Siempre CMS
	require_once('jstree.class.db.php');
	require_once('jstree.class.tree.php');
	

		
?>
