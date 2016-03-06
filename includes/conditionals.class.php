<?php
/*
 * This file is part of Siempre CMS
 *
 * (c) 2015 Steve Morgan http://siempresolutions.co.uk/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
class Conditional {

	public $conditionalStr;
	public $conditionalStrLen;
	public $conditionalArray;
	public $operandLeft = '';
	public $operandLeftIsCompound = false;
	public $operandLeftToNegate = false;
	public $operandRight = '';
	public $operandRightIsCompound = false;
	public $operandRightToNegate = false;
	public $operator;
	public $nextPos = 0;
	public $result;	
	public $error = false;
	
	function __construct($conditionalStr) {
		//error_log("Conditional String: " . $conditionalStr);
		$this->conditionalStr = trim($conditionalStr);
		$this->conditionalStrLen = strlen($this->conditionalStr);
		$this->conditionalArray = str_split($this->conditionalStr);
		
		
		// find the left operand, the operator and then the right operand
		$this->findOperandOrCompound($this->operandLeft, $this->operandLeftIsCompound, $this->operandLeftToNegate);
		
		if($this->nextPos >= $this->conditionalStrLen - 1 ){
			$this->operator = "N/A";
			$this->operandRight = "N/A";
		} else {
			$this->findOperator();
			$nextPosBeforeRight = $this->nextPos;
			$this->findOperandOrCompound($this->operandRight, $this->operandRightIsCompound, $this->operandRightToNegate);
		}
		// check we're at the end of the conditional str o/w it could be (3 == 44 && somethingelse)
	//	echo ("next: " . $this->nextPos . " - " . strlen($conditionalStr));
		if ($this->nextPos < $this->conditionalStrLen -1) {
			$this->operandRight = substr($this->conditionalStr, $nextPosBeforeRight);
			$this->operandRightIsCompound = true;
		}
		
	/*	echo "<p>******************</p>";
		echo "<p>$this->conditionalStr</p>";
		echo "<p>LEFT:*" . $this->operandLeft . "* - Compound: " . $this->operandLeftIsCompound. "</p>";
		echo "<p>OPERATOR:*" . $this->operator ."*</p>";
		echo "<p>RIGHT:*" .  $this->operandRight . "* - Compound: " . $this->operandRightIsCompound. "</p>"; */

		
		// if we have a compound then evaluate this by creating a new child instance of this class
		if ($this->operandLeftIsCompound) {
			$childConditional = new Conditional($this->operandLeft);
			$this->operandLeft = $childConditional->result;
		}
		if ($this->operandRightIsCompound) {
			$childConditional = new Conditional($this->operandRight);
			$this->operandRight = $childConditional->result;
		} 
	
		// now evaluate the conditional (all compound child conditionals should be resolved here)
		$this->evaluateConditional();
		//if ($this->negate) {
		//	$this->result = !$this->result;
		//}
	//	echo "<p>RESULT: " .  $this->result . "</p>";
	}
	
	function findOperator() {	
		// != == <= >= < > && ||
		$curCharPos = $this->nextPos;
		while($curCharPos < $this->conditionalStrLen) {
			$curChar = $this->conditionalArray[$curCharPos];
			if($curChar != ' '){
				break;
			}
		$curCharPos += 1;
		}
		$opChar1 = $this->conditionalStr[$curCharPos];
		$opChar2 = $this->conditionalStr[$curCharPos + 1];
		
		if($opChar1 == "=" || $opChar1 == "!" || $opChar1 == "<" || $opChar1 == ">") {
			// check for != >= <=
			if ($opChar2 == "=") {
				$this->operator = substr($this->conditionalStr, $curCharPos, 2);
				$this->nextPos = $curCharPos + 2;
			} // else it's a single > or <
			else {
				$this->operator = substr($this->conditionalStr, $curCharPos, 1);
				$this->nextPos = $curCharPos + 1;
			}	
		}  // Check for OR ||
		elseif ($opChar1 == "|" && $opChar2 == "|") {
			$this->operator = substr($this->conditionalStr, $curCharPos, 2);
			$this->nextPos = $curCharPos + 2;
		}  // Check for AND &&		
		elseif ($opChar1 == "&" && $opChar2 == "&") {
			$this->operator = substr($this->conditionalStr, $curCharPos, 2);
			$this->nextPos = $curCharPos + 2;
		} else {
			$this->error = true;
		}		
	}

	
	function findOperandOrCompound(&$operand, &$isCompound, &$toNegate) {
		// looks for either the first operand in the string or a bracketted expression
		$startPos = $this->nextPos;
		$endPos = 0;
		$openBracket = false; // open bracket mode (only once)
		$openBracketCount = false; // open bracket found (only once)
		$openDQuote = false; // string mode
		$openSQuote = false; // string mode
	
		$curCharPos = $this->nextPos;
		
		// first check for a negate !
		if ($curCharPos < $this->conditionalStrLen && $this->conditionalArray[$curCharPos] == '!') {
			$toNegate = true;
			$startPos += 1;
			$curCharPos += 1;
		}

		// move past spaces first - only an issue in the middle of strings as we trim
		$curChar = '';
		if($this->conditionalArray[$curCharPos] == ' ') {
			while($curCharPos < $this->conditionalStrLen && $curChar != ' ') {
				$curChar = $this->conditionalArray[$curCharPos];
				$curCharPos += 1;
			}
			$startPos = $curCharPos;
		}
		
		// we check at the end peeking forward if there is another operator (e.g. a compound)
		$foundAnotherOperator = false;
		$peekCharPos = 0;
		
		while($curCharPos < $this->conditionalStrLen) {
			$curChar = $this->conditionalArray[$curCharPos];
		//	  echo "<p>" . $curCharPos . " - " .$curChar . "</p>";
			// if we're in string mode and it's not the closing string then NEXT - o/w we could match a bracket in the string!
			// Check for Escape strings :( "\"tr\"ue"
			
			// Double quote mode?
			if($openDQuote) {
				if ($curChar == '"' && $curCharPos > 0 && ($this->conditionalArray[$curCharPos - 1]  != '\\')) {
					$openDQuote = false;
					$curCharPos += 1;
					continue;
				} else {
					$curCharPos += 1;
					continue;
				}
			}
			// single quote mode?
			if($openSQuote) {
				if ($curChar == "'"  && $curCharPos > 0 && ($this->conditionalArray[$curCharPos - 1]  != '\\')) {
					$openSQuote = false;
					$curCharPos += 1;
					continue;
				} else {
					$curCharPos += 1;
					continue;
				}
			}
			
			// not in quote mode so check for a quote
			// double quote "
			if($curChar == '"') {
				$openDQuote = true;
				$curCharPos += 1;
				continue;
			}	
			// single quote '
			if($curChar == "'") {
				$openSQuote = true;
				$curCharPos += 1;
				continue;
			}	
			
			if ($curChar == '(') {
				$openBracket = true;
				$openBracketCount += 1;
				$isCompound = true; // we have a compound. Might even just be a (operator) but we'll reprocess to remove brackets
				$curCharPos += 1;
				continue;
			}
			
			// if closing bracket check the count. 
			if($curChar == ')') {
				$openBracketCount--;
				if ($openBracketCount == 0) {
					$curCharPos += 1;
					break; 
				} else {
					$curCharPos += 1;
					continue;
				}	
			}
			
			// If we have an open bracket and no closing bracket and we're not in string mode then move on
			if ($openBracketCount > 0) {
				$curCharPos += 1;
				continue;
			}
			
			// if we've found an operator ...
			if($curChar == "=" || $curChar == "!" || $curChar == ">" || $curChar == "<" || $curChar == "&" || $curChar == "|") {
				// first check that there isn't another operator (if there is then we have something like a == b && c == d)
				// we had a bug where the left was a and the right was b && c == d 
				// when we should have had a compound on the left first . 
				$peekCharPos = $curCharPos + 2; // +2 to move past the operator
				while($peekCharPos < $this->conditionalStrLen) 
				{
					$curPeekChar = $this->conditionalArray[$peekCharPos];
					
					if($curPeekChar == "&" || $curPeekChar == "|")
					{ 
						$isCompound = true;
						$endPos = $peekCharPos;
						$foundAnotherOperator = true;
						break;
					}
					// TODO - not sure about this bit of logic - I think I should break  out here with a false... to test
					if($curPeekChar == ")" || $curPeekChar == "(")
					{
						break;
					}
					
					$peekCharPos++;
				}  
				if(!$foundAnotherOperator)
				{
					$endPos = $curCharPos;
				}
				break;
			}
				
			$curCharPos += 1;
		}
		if($foundAnotherOperator)
		{
			$endPos = $peekCharPos;
			$this->nextPos = $peekCharPos;
		}
		else 
		{
			$endPos = $curCharPos;
			$this->nextPos = $curCharPos;
		}
		
		if ($openBracket) {
			$operand = trim(substr($this->conditionalStr, $startPos+1, $endPos - $startPos - 2)); // strip brackets
		} else {
			$operand = trim(substr($this->conditionalStr, $startPos, $endPos - $startPos));
		}
		return;
	}
	
	function evaluateConditional() {
		// there was a big error .. problem was in trying to deal with compound conditionals and string comparisons
		// I'd added string deliminators to the start and end of the field values. This means here that $this->operandLeft/Right comes through with the value '"1"' 
		// and then this is not being correctly interpetted as true
		// HACK to try this to fix it?
		
		// check if we're doing a string to string comparison?
		if(strlen($this->operandLeft) >= 2 && substr($this->operandLeft, 0, 1) == '"' && substr($this->operandLeft, -1) == '"' &&
			strlen($this->operandRight) >= 2 && substr($this->operandRight, 0, 1) == '"' && substr($this->operandRight, -1) == '"')
			{
				// we don't do any of the boolean conversion
				$operandLeft = $this->operandLeft;
				$operandRight = $this->operandRight;
			}
			else 
			{
				// convert strings to bool?  NOT SURE ABOUT THIS!
				if(strlen($this->operandLeft) >= 3 && substr($this->operandLeft, 0, 1) == '"' && substr($this->operandLeft, -1) == '"')
				{
					$operandLeft = substr($this->operandLeft, 1, -1);
				}
				else
				{
					$operandLeft = $this->operandLeft;
				}
				if(strlen($this->operandRight) >= 3 && substr($this->operandRight, 0, 1) == '"' && substr($this->operandRight, -1) == '"')
				{
					$operandRight = substr($this->operandRight, 1, -1);
				}
				else
				{
					$operandRight = $this->operandRight;
				}
				
				if($this->operator == "N/A"){
					$this->result = filter_var(strval($operandLeft), FILTER_VALIDATE_BOOLEAN);
					
					if ($this->operandLeftToNegate) {
						$this->result = !$this->result;
					}
					return true;
				}
				
				//error_log("Checking: " . $this->operator);
				//echo "<p>*LEFT: ".$this->operandLeft."</p>";
				//echo "<p>*OPERATOR: ".$this->operator."</p>";
				//echo "<p>*RIGHT: ".$this->operandRight."</p>";
				// only for use in the boolean comparisons
				$operandLeft = filter_var($operandLeft, FILTER_VALIDATE_BOOLEAN);
				if ($this->operandLeftToNegate) {
						$operandLeft = !$operandLeft;
					}
				$operandRight = filter_var($operandRight, FILTER_VALIDATE_BOOLEAN);
				if ($this->operandRightToNegate) {
						$operandRight = !$operandRight;
				}
			}
		
		
		switch($this->operator) {
			case "==":
				if ($operandLeft == $operandRight) {
					$this->result = true;
				}
				break;
			case "!=":
				if ($operandLeft != $operandRight) {
					$this->result = true;
				}
				break;
			case "<=":
				if ($operandLeft <= $operandRight) {
					$this->result = true;
				}
				break;
			case ">=":
				if ($operandLeft <= $operandRight) {
					$this->result = true;
				}
				break;
			case "&&":
				if ($operandLeft && $operandRight) {	
					$this->result = true;
				}
				break;
			case "||":
				if ($operandLeft || $operandRight) {
					$this->result = true;
				}
				break;
			default:
				$this->result = false;
				$this->error = true;
				
				error_log("Invalid Operator found in - " . $this->operandLeft . $this->operator .  $this->operandRight);
				error_log("Conditional = " .  $this->conditionalStr);
				
		}
	}
}
?>