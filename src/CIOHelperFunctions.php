<?php
class CIOHelperFunctions{

	var $errorMessage;
	
	/* Send an xml stream to CIO Remote Hub */
	function sendXML($xmlbuf, $log_obj){
		try{
			if (function_exists('curl_init')){
				$type = "Curl";
				$c = curl_init('https://cioremotehub.ciotech.com/api.php');
				curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($c, CURLOPT_POST, true);
				curl_setopt($c, CURLOPT_POSTFIELDS, $xmlbuf);
				curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
				$returnxmlbuf = curl_exec($c);
				curl_close($c);
			}
			else{
				$type = "OpenSSL";
				$send = array('http' =>
					array(
						'method'  => 'POST',
						'header'  => 'Content-type: application/rss+xml',
						'Content-length: '.strlen($xmlbuf),
						'content' => $xmlbuf
					));
				$context = stream_context_create($send);
				$returnxmlbuf = file_get_contents('https://cioremotehub.ciotech.com/api.php', false, $context);
			}
		}catch(Exception $e){
			//error('Error sending XML - '.$e->getMessage(), $log_obj);
			return false;
		}
		//echo $this->text_for_web($returnxmlbuf)."<br>";
		$xml = @new SimpleXMLElement($returnxmlbuf);
		//echo $xml->Response->Status['text']."<br>";
		
		$result = (String)$xml->Response->Status['code'];
		$resultText = (String)$xml->Response->Status['text'];
		unset($xmlbuf);
		if ($result == '200'){
			return true; 
		}
		else{
			$this->errorMessage = $resultText;
			return false;
		}
	}
	
	/**
	 * Convert array to xml and add to an existing xmlWriter.
	 * No return value as this function writes directly to the object.
	 * Throws Exception on error.
	 */
	public static function array_to_xml($array, &$xml_object) {
		foreach($array as $key => $value) {
			if(is_array($value)) {
				if($key === '@attributes'){
					foreach($value as $key => $value){
						$xml_object->startAttribute("$key");
							$xml_object->text("$value");
						$xml_object->endAttribute();
					}
				}
				else if(!is_numeric($key) || intval($key) - $key != 0){
					$xml_object->startElement("$key");
						helperFunctions::array_to_xml($value, $xml_object);
					$xml_object->endElement();
				}
				else{
					helperFunctions::array_to_xml($value, $xml_object);
				}
			}
			else {
				$content = (trim("$value") != '') ? "$value" : null;
	        	$xml_object->writeElement("$key", $content);
			}
		}
	}
	
	/**
	 * Convert an object(like SimpleXMLObject) into an array. (Taken from: http://www.php.net/manual/en/book.simplexml.php#97555)
	 * @param object $arrObjData	Object to convert into array
	 * @param array $arrSkipIndices	Array of indice values to skip
	 * @return array	An array representation of the original object
	 */
	public static function object_to_array($arrObjData, array $arrSkipIndices = array())
	{
		$arrData = null;

		// if input is object, convert into array
		if(is_object($arrObjData) && get_class($arrObjData) == 'SimpleXMLElement'){
			if ($arrObjData->count()){
				$temp = null;
				// Get any attributes
				foreach($arrObjData->attributes() as $name => $value) {
					$temp['@attributes'][$name] = $value;
				}
				
				foreach($arrObjData->children() as $name=>$node){
					if ($node->count() && trim((string)$node)){
						$node->addChild($name, (string)$node);
					}
					if (is_array($temp[$name])){
						$temp[$name][] = $node;
						
					}
					else if (isset($temp[$name])){
						$temp[$name] = array($temp[$name], $node);
					}
					else{
						$temp[$name] = $node;
					}
				}
			}
			else{
				$temp = (string)$arrObjData;
			}
			$arrObjData = $temp;
		}
		else if (is_object($arrObjData)) {
			$arrObjData = get_object_vars($arrObjData);
		}
		
		//echo '!'.print_r($arrObjData, true)."<br><br>";

		if (is_array($arrObjData)) {
			foreach ($arrObjData as $index => $value) {
				if (is_object($value) || is_array($value)) {
					$value = helperFunctions::object_to_array($value, $arrSkipIndices); // recursive call
				}
				if (in_array($index, $arrSkipIndices)) {
					continue;
				}
				$arrData[$index] = $value;
			}
		}
		else{
			$arrData = $arrObjData;
		}
		return $arrData;
	}
	
	/**
	 * Prepare text for web screen.
	 *	- Converts html entities to their codes
	 *	- Converts newlines to 'br' and tabs to 4 spaces
	 * @param string $string_to_prepare
	 */
	public static function text_for_web($string_to_prepare){
		return preg_replace('/(\t)/','&nbsp;&nbsp;&nbsp;&nbsp;',nl2br(htmlentities($string_to_prepare)));
	}


	/**
	 * Print out a stack trace from entry point to wherever this function was called.
	 * @param boolean $show_args Show arguments passed to functions? Default False.
	 * @param boolean $for_web Format text for web? Default True.
	 * @param boolean $return Return result instead of printing it? Default False.
	 */
	public static function stack_trace($show_args=false, $for_web=true, $return=false){
		if ($for_web){
			$before = '<b>';
			$after = '</b>';
			$tab = '&nbsp;&nbsp;&nbsp;&nbsp;';
			$newline = '<br>';
		}
		else{
			$before = '<';
			$after = '>';
			$tab = "\t";
			$newline = "\n";
		}
		$output = '';
		$ignore_functions = array('include','include_once','require','require_once');
		$backtrace = debug_backtrace();
		$length = count($backtrace);
		
		for ($i=0; $i<$length; $i++){
			$function = $line = '';
			$skip_args = false;
			$caller = @$backtrace[$i+1]['function'];
			// Display function called (if not a require or include)
			if(isset($caller) && !in_array($caller, $ignore_functions)){
				$function = ' in function '.$before.$caller.$after;
			}
			else{
				$skip_args = true;
			}
			$line = $before.$backtrace[$i]['file'].$after.$function .' on line: '.$before.$backtrace[$i]['line'].$after.$newline;
			if ($i < $length-1){
				if ($show_args && $backtrace[($i+1)]['args'] && !$skip_args){
					$params = ($for_web) ? htmlentities(print_r($backtrace[($i+1)]['args'], true))
							: print_r($backtrace[($i+1)]['args'], true);
					$line .= $tab.'Called with params: '.preg_replace('/(\n)/',$newline.$tab,trim($params)).$newline.$tab.'By:'.$newline;
					unset($params);
				}
				else{
					$line .= $tab.'Called By:'.$newline;
				}
			}
			if ($return){
				$output .= $line;
			}
			else{
				echo $line;
			}
		}
		if ($return){
			return $output;
		}
	}
	
	/**
	 * Checks if $n is an integer between $min and $max, inclusive of $min and exclusive
	 * of $max. [$min, $max).
	 * Notice: Oracle may sue us for having a range check function.
	 * 
	 * @param type $min
	 * @param type $max
	 */
	public static function between($n, $min, $max){
		return isset($n) && intval($n) >= $min && intval($n) < $max;
	}
	
	/**
	 * Write an error to a log file determined by $type
	 * Depends on CIOLog.php
	 * @param string $message Message to write
	 * @param string $type Log file to write to
	 * @param boolean $echo Echo out error? Default false
	 * @param boolean $stack_trace Include stack trace? Default false
	 */
	public static function write_error_to_log($message, $type='General', $echo=false, $stack_trace=false){
		if ($stack_trace){
			$message = $message."\nStack trace: ".helperFunctions::stack_trace(false, false, true);
		}
		if ($echo){
			echo helperFunctions::text_for_web($message).'<br>';
		}
		require_once("CIOLog.php");
		$log_obj = new CIOLog("./log/",$type,"monthly");
		$log_obj->write_log(str_replace("\r", "", $message));
	}
}
?>