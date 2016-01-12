<?php 
/*
 * This file is part of Siempre CMS
 *
 * (c) 2015 Steve Morgan http://siempresolutions.co.uk/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// function creates guids for non-windows servers (sigh)
function guid(){
   if (function_exists('com_create_guid')){
       return com_create_guid();
   }else{
       mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
       $charid = strtoupper(md5(uniqid(rand(), true)));
       $hyphen = chr(45);// "-"
       $uuid = chr(123)// "{"
               .substr($charid, 0, 8).$hyphen
               .substr($charid, 8, 4).$hyphen
               .substr($charid,12, 4).$hyphen
               .substr($charid,16, 4).$hyphen
               .substr($charid,20,12)
               .chr(125);// "}"
       return $uuid;
   }
}

// TODO check this allows for apostrophes in emails
function isEmail($email){
	return (preg_match("/^(\w+((-\w+)|(\w.\w+))*)\@(\w+((\.|-)\w+)*\.\w+$)/",$email));
}

function isValidGUID($guid){

if (preg_match("/^(\{)?[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}(?(1)\})$/i", $guid) === 1)
	return true;
else
	return false;
}

// mainly used in the create dialogs when creating performances or notes. 	
function flipdate($dt, $seperator_in = '-', $seperator_out = '-')
{
	return implode($seperator_out, array_reverse(explode($seperator_in, $dt)));
}


?>