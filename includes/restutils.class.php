<?php 
/*
 * This file is part of Siempre CMS
 *
 * (c) 2015 Steve Morgan http://siempresolutions.co.uk/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
// lots of credit to http://www.gen-x-design.com/archives/create-a-rest-api-with-php/


class RestUtils
{
	public static function processRequest()
	{
		// get our verb
		$request_method = strtolower($_SERVER['REQUEST_METHOD']);
		$return_obj		= new RestRequest();
		// we'll store our data here
		$data			= array();
			
		$pathArray = array();
		$pathURIArray = array();
		$pathURIArray = parse_url(strtolower($_SERVER["REQUEST_URI"]));  // breaks the query string and path out
		
		// strip leading '/' TO DO - I was stripping the leading '/' - why was this? Win / Linux diff?
		// $path = $pathURIArray['path'];
	//	if (strlen($path) > 1 && substr($path,0,1) ==='/') {
	//		$path = substr($path, 1, strlen($path)-1);
	//	}
		// modified to use trim and to strip the first and final /
		$path = trim($pathURIArray['path'],'/');
		
		$pathArray = explode("/", $path);
		// error_log(print_r($pathArray,true));	
		$return_obj->setPathArray($pathArray);
		
		// Get the URL variables / POST variables 
		switch ($request_method)
		{
			// gets are easy...
			case 'get':
				// it's not as simple is the example I started from.  I'm looking for a ? in the URI as couldn't just use isset($_GET). 
				//error_log($pathURI . ' - ' . strpos($pathURI, '?'));
				if(strpos($_SERVER["REQUEST_URI"], '?') !== FALSE) {
					$data = $_GET;
					//error_log (print_r($_GET,true));
					//error_log (print_r($data,true));
				}
//				else {	
//					$data = $pathURIArray;
//				}	
				break;
			// so are posts
			// TO DO TEST THIS
			case 'post':
				$data = $_POST;
				break;
			// here's the tricky bit...
			// TO DO TEST THIS
			case 'put':
				// basically, we read a string from PHP's special input location,
				// and then parse it out into an array via parse_str... per the PHP docs:
				// Parses str  as if it were the query string passed via a URL and sets
				// variables in the current scope.
				parse_str(file_get_contents('php://input'), $put_vars);
				$data = $put_vars;
				break;
		}

		// store the method
		$return_obj->setMethod($request_method);

		// set the raw data, so we can access it if needed (there may be other pieces to your requests)
		$return_obj->setRequestVars($data);
		
		return $return_obj;
	}


	public static function sendResponse($status = 200, $body = '', $content_type = 'text/html')  
	{  
		$status_header = 'HTTP/1.1 ' . $status . ' ' . RestUtils::getStatusCodeMessage($status);  
		// set the status  
		header($status_header);  
		
		if($status == 301 || $status == 302) {
			header("Location: " . $DOMAIN . '/' . $body); 
		} else {
			// set the content type  
			header('Content-type: ' . $content_type);  
		}
	  
		// pages with body are easy  
		if($body != '')  
		{  
			// send the body  
			echo $body;  
			exit;  
		}  
		// we need to create the body if none is passed  
		else  
		{  
			// create some body messages  
			$message = '';  
	  
			switch($status)  
			{  
				case 401:  
					$message = 'You must be authorized to view this page.';  
					break;  
				case 404:  
					$message = 'The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found.';  
					break;  
				case 500:  
					$message = 'The server encountered an error processing your request.';  
					break;  
				case 501:  
					$message = 'The requested method is not implemented.';  
					break;  
			}  
	  
			// servers don't always have a signature turned on (this is an apache directive "ServerSignature On")  
			$signature = ($_SERVER['SERVER_SIGNATURE'] == '') ? $_SERVER['SERVER_SOFTWARE'] . ' Server at ' . $_SERVER['SERVER_NAME'] . ' Port ' . $_SERVER['SERVER_PORT'] : $_SERVER['SERVER_SIGNATURE'];  
	  
			$body = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">  
						<html>  
							<head>  
								<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>  
								<title>' . $status . ' ' . RestUtils::getStatusCodeMessage($status) . '</title>  
							</head>  
							<body>  
								<p>This page has an error</p>
								<h1>' . RestUtils::getStatusCodeMessage($status) . '</h1>  
								<p>' . $message . '</p>  
								<hr />  
								<address>' . $signature . '</address>  
							</body>  
						</html>';  

			echo $body;  
			exit;  
		}  
	}  

	public static function getStatusCodeMessage($status)
	{
		$codes = Array(
		    100 => 'Continue',
		    101 => 'Switching Protocols',
		    200 => 'OK',
		    201 => 'Created',
		    202 => 'Accepted',
		    203 => 'Non-Authoritative Information',
		    204 => 'No Content',
		    205 => 'Reset Content',
		    206 => 'Partial Content',
		    300 => 'Multiple Choices',
		    301 => 'Moved Permanently',
		    302 => 'Found',
		    303 => 'See Other',
		    304 => 'Not Modified',
		    305 => 'Use Proxy',
		    306 => '(Unused)',
		    307 => 'Temporary Redirect',
		    400 => 'Bad Request',
		    401 => 'Unauthorized',
		    402 => 'Payment Required',
		    403 => 'Forbidden',
		    404 => 'Not Found',
		    405 => 'Method Not Allowed',
		    406 => 'Not Acceptable',
		    407 => 'Proxy Authentication Required',
		    408 => 'Request Timeout',
		    409 => 'Conflict',
		    410 => 'Gone',
		    411 => 'Length Required',
		    412 => 'Precondition Failed',
		    413 => 'Request Entity Too Large',
		    414 => 'Request-URI Too Long',
		    415 => 'Unsupported Media Type',
		    416 => 'Requested Range Not Satisfiable',
		    417 => 'Expectation Failed',
		    500 => 'Internal Server Error',
		    501 => 'Not Implemented',
		    502 => 'Bad Gateway',
		    503 => 'Service Unavailable',
		    504 => 'Gateway Timeout',
		    505 => 'HTTP Version Not Supported'
		);

		return (isset($codes[$status])) ? $codes[$status] : '';
	}
}

class RestRequest
{
	private $request_vars;   
	private $http_accept;
	private $method;
	private $pathArray;
	
	public function __construct()
	{
		$this->request_vars		= array();
	//	$this->http_accept		= 'xml';//(strpos($_SERVER['HTTP_ACCEPT'], 'json')) ? 'json' : 'xml';
		$this->http_accept		= (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'json')) ? 'json' : 'xml';
		$this->method			= 'get';
		$this->requestFunction 	= '';
		$this->pathArray		= array();
	}

	// sjm added
	public function setRequestFunction($requestFunction)
	{
		$this->requestFunction = $requestFunction;
	}
	
	public function setMethod($method)
	{
		$this->method = $method;
	}

	public function setRequestVars($request_vars)
	{
		$this->request_vars = $request_vars;
	}
	
	public function setPathArray($pathArray)
	{
		$this->pathArray = $pathArray;
	}
	
	// sjm added
	public function getRequestFunction()
	{
		return $this->requestFunction;
	}
	
	public function getMethod()
	{
		return $this->method;
	}

	public function getHttpAccept()
	{
		return $this->http_accept;
	}

	public function getRequestVars()
	{
		return $this->request_vars;
	}
	
	public function getPathArray()
	{
		return $this->pathArray;
	}
}
?>