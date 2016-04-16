<?php
/*
 * This file is part of Siempre CMS
 *
 * (c) 2015 Steve Morgan http://siempresolutions.co.uk/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

	// Gets and checks the version (ensures DB is up to date with the files)
	// updates are only checked in the home dashboard - this just avoids errors / corrupt data
  
	// DB Version Number
	$vercheck = new vercheck();
	
	
	define('DBVERSION', $vercheck->getDBVersion());
	
	// File version number
	define('FILEVERSION', '1.3.6');
	
	// if not equal - redirect to upgrade page?
	// TO DO for now just shows an error - upgrading is not yet automated
	if(DBVERSION !== FILEVERSION)
	{
		header('Location: upgrade.php?msg=version_error&dbversion='.DBVERSION.'&fileversion='.FILEVERSION);
	}
		
?>
