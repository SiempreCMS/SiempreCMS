	<?php
/*
 * This file is part of Siempre CMS
 *
 * (c) 2015 Steve Morgan http://siempresolutions.co.uk/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
 
//  Purpose:-      Cache for the admin pages - only really drops the cache on publish

	
class CMSCache {

	function __construct() 
	{
	}
	
	public function dropAllCache()
	{
		// used as a quick and dirty clean up on publish in the admin - TODO change this to only drop related pages?
		$dir = "../../cache/pages/";
		$di = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
		$ri = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);
		foreach ( $ri as $file ) {
			$file->isDir() ?  rmdir($file) : unlink($file);
		}
		return true;
	}
}