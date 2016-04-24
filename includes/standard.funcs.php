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


// validates email address - TO DO check this is the latest one I used on cycle manchester
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
/* function flipdate($dt, $seperator_in = '-', $seperator_out = '-')
{
	return implode($seperator_out, array_reverse(explode($seperator_in, $dt)));
} */


function findMatchingTags($inputString, $openTag, $closeTag, &$openTagPos, &$closeTagPos, &$content) {
	// takes a string and finds the tags passed in 
	// returns the start and end pos and the content within the tags

	$openDQuote = false;
	$openSQuote = false;
	$curCharPos = 0;
	$tagCharPos = 0;
	$openTagCount = 0;
	
	$openTagLen = strlen($openTag);
	$closeTagLen = strlen($closeTag);
	
	// find open tag
	while($curCharPos < strlen($inputString)) {
			
		$curChar = $inputString[$curCharPos];
		
		// if we're in string mode and it's not the closing tag then NEXT - o/w we could match a bracket in a string!
		// Check for escaped strings :( "\"tr\"ue"
		
		// Double quote mode?
		if($openDQuote) {
			if ($curChar == '"'  && $curCharPos > 0 && $inputString[$curCharPos - 1]  != '\\') {
				$openDQuote = false;
				$endPos = $curCharPos;
				$curCharPos += 1;
				continue;
			} else {
				$curCharPos += 1;
				continue;
			}
		}
		// single quote mode?
		if($openSQuote) {
			if ($curChar == "'"  && $curCharPos > 0 && $inputString[$curCharPos - 1]  != '\\') {
				$openSQuote = false;
				$endPos = $curCharPos;
				$curCharPos += 1;
				continue;
			} else {
				$curCharPos += 1;
				continue;
			}
		}
		
		// not in quote mode so check for quotes - making sure the char before is NOT an escape symbol
		// double quote "
		if($curChar == '"' && ($curCharPos == 0 || $curCharPos > 0 && $inputString[$curCharPos - 1]  != '\\')) {
			$openDQuote = true;
			$curCharPos += 1;
			continue;
		}	
		// single quote '
		if($curChar == "'" && ($curCharPos == 0 || $curCharPos > 0 && $this->conditionalArray[$curCharPos - 1]  != '\\')) {
			$openSQuote = true;
			$curCharPos += 1;
			continue;
		}	
		
		// if we've already found an opening tag then we're looking for a closing tag
		if ($openTagCount > 0 && $curChar == $closeTag[0]) {
			// while to check tag match
			$tagCharPos = 0;
			$found = true;
			while($tagCharPos < $closeTagLen) {
				if($inputString[$curCharPos + $tagCharPos] != $closeTag[$tagCharPos]) {
					$found = false;
					break;
				}
				$tagCharPos += 1;
			}
			if($found) {
				$openTagCount -= 1;	
				// if we've found a matching closing tag
				if($openTagCount == 0) {
					$closeTagPos = $curCharPos + $closeTagLen;
					$startPos = $openTagPos + $openTagLen;
					$strLen = $closeTagPos - $startPos - $closeTagLen;
					$content = substr($inputString, $startPos, $strLen);
	
					return true;
				}
			}
		}  
		
		// check for open tag
		if ($curChar == $openTag[0]) {
			// while to check tag match
			$tagCharPos = 0;
			$found = true;
			while($tagCharPos < $openTagLen) {
				if($inputString[$curCharPos + $tagCharPos] != $openTag[$tagCharPos]) {
					$found = false;
					break;
				}
				$tagCharPos += 1;
			}
			if($found) {
				if($openTagCount == 0) {
					$openTagPos = $curCharPos;
				}
				$openTagCount += 1;
			}
		}  
		
		$curCharPos += 1;
	}
	return false; // not found
}

function trim_lines( $str , $what = NULL , $with = ' ' )
{
    if( $what === NULL )
    {
        //  Character      Decimal      Use
        //  "\0"            0           Null Character
        //  "\t"            9           Tab
        //  "\n"           10           New line
        //  "\x0B"         11           Vertical Tab
        //  "\r"           13           New Line in Mac
        //  " "            32           Space
       
        $what   = "\\x10-\\x10";    // new lines
		 $what   = "\\x10-\\x20";    //all white-spaces and control chars
	//	$what = '#\R+#';
    }
   
    return trim( preg_replace( "/[".$what."]+/" , $with , $str ) , $what );
}

function pos_in_multiarray($elem, $array, $field)
{
    $top = sizeof($array) - 1;
    $bottom = 0;
	$count = 0;
    while($bottom <= $top)
    {
		$curElem = $array[$bottom][$field];
        if(trim($array[$bottom][$field]) == trim($elem))
		{
			return $count;
		}

        else 
		{
            if(is_array($array[$bottom][$field]))
			{
                if(pos_in_multiarray($elem, ($array[$bottom][$field])))
				{
                    return $count;
				}
			}
		}

        $bottom++;
		$count++;
    }        
    return -1;
}

?>