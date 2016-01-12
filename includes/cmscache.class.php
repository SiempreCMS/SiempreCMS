<?php
/*
 * This file is part of Siempre CMS
 *
 * (c) 2015 Steve Morgan http://siempresolutions.co.uk/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
 
//  Purpose:-      First go at CMS Cache - general workings are that if a file exists
// it's returned as the page.
// if not the new check the DB as normal. It might be that the page is not allowed to be cached - if so then
// it's fine as the file won't be written out. 
// todo - guessing the checking of an existance of a file is less intensive than hitting a DB?

class CacheItem {
	public $expiry;
	public $created;
	public $content;
	public $filePathAndFileName;
	
	function __construct($filePathAndFileName, $content)
	{
		$seconds_to_add = CACHE_LENGTH;
		$this->filePathAndFileName = $filePathAndFileName;
		$this->content = $content;
		$date = new DateTime();
		$this->created = $date->format('Y-m-d H:i:s');
		$this->expiry = $date->add(new DateInterval('PT0H' . $seconds_to_add . 'S'))->format('Y-m-d H:i:s');
	}
}

class CMSCache {
	public $inCache;
	public $cacheItem;
	private $pathArray;
	private $filePath;
	private $filePathAndFileName;


	function __construct($pathArray) 
	{
		$this->pathArray = $pathArray;
		$this->inCache = false;
		
		$this->getFilePath();
		$this->getCache();
	}
	
	
	private function getFilePath() {
		if(count($this->pathArray) == 1 && $this->pathArray[0] == ''){
			$this->filePath = '../cache/pages/';
			$this->filePathAndFileName = '../cache/pages/page.txt';
		} 
		else {
			$this->filePathAndFileName = '../cache/pages/' . implode ('/', $this->pathArray) . '/'. 'page.txt';
			$this->filePath = '../cache/pages/' . implode ('/', $this->pathArray) . '/';	
		}
		
		return;
	}
	
	
	private function getCache() {
		// checks the cache location
		if (file_exists($this->filePathAndFileName))
		{
			// to do read the cache
			$fileContents = file_get_contents($this->filePathAndFileName);
			$this->cacheItem = unserialize($fileContents);
				
			// if expiry date time is not passed
			$date = new DateTime();
		
			if($this->cacheItem->expiry > $date->format('Y-m-d H:i:s')) {
				$this->inCache = true;
			}
		}
	}
	
	
	public function writeCache($content)
	{
		$this->dropCache($this->filePathAndFileName);
		
		// to do - will I pass the content to the constructor?
		$cacheItem = new CacheItem($this->filePathAndFileName, "");
		$cacheItem->content = $content;//"<html><body><h1>Hi world</h1><h2>Created:".$cacheItem->created."<h2><h2>Expiry:".$cacheItem->expiry."<h2></body></html>";
		
		$serialisedCacheItem = serialize($cacheItem);
		
		// check the dir exists if not create it recursively
		// is_dir() is faster than file_exists (?)
		if (!file_exists($this->filePath)) {
			if(!mkdir($this->filePath, 0777, true))
				error_log("Failed to create directory for cache - " . $this->filePath);
		}

		if(!file_put_contents($this->filePathAndFileName, $serialisedCacheItem))
		{
			error_log('Cannot write cache - file path is not writeable : ' . $this->filePath);
		}
	}
	
	
	
	public function dropCache($filePathAndFileName)
	{
		// error_log("Attempting delete : " . $this->filePath);
		// suppressing the error message which is hacky but heyho
		@unlink($filePathAndFileName);
	}
	
	public function dropAllCache()
	{
		// not using here - on page publish in the admin
		$dir = "../cache/pages/";
		$di = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
		$ri = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);
		foreach ( $ri as $file ) {
			$file->isDir() ?  rmdir($file) : unlink($file);
		}
		return true;
	}
}