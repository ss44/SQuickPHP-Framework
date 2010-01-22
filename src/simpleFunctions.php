<?php
/**
 * Collection of commonly used functions to be included in our project.
 * @author Shajinder Padda <shajinder@gmail.com
 * @created 28-June-2008
 * @modified 29-May-2009
 */

/**
 * Outputs basic debug info with / pre tags attached.
 * @param mixed $var  The variable to output.
 * @param bool $showVarDump By default uses a print_r unless specified to use var_dump
 */

/**
 * Debug tools
 */
if (!function_exists('oops')){
	function oops( $vars, $varDump = false, $level = 0 ){
	        $dbg = debug_backtrace();
	
	        $file = $dbg[0]['file'];
	        $line = $dbg[0]['line'];
	        echo "<pre><H1>". $file ." @ ". $line ."</H1>";
	
	        for ($x = 1; $x < $level; $x++){
	                if (isset($dbg[$x])){
	                        $file = $dbg[$x]['file'];
	                        $line = $dbg[$x]['line'];
	                        echo "<center><H3>". $file ." @ ". $line ."</H3></center>";
	                }
	        }
	
	        if ($varDump) var_dump($vars);
	        else print_r($vars);
	        echo "</pre>";
	}
}
if (!function_exists('dim')){
	//Die improved merges oops with die
	function dim( $vars, $varDump = false, $level = 0 ){
		$dbg = debug_backtrace();
	
		$file = $dbg[0]['file'];
		$line = $dbg[0]['line'];
		echo "<pre><H1>". $file ." @ ". $line ."</H1>";
	
		for ($x = 1; $x < $level; $x++){
				if (isset($dbg[$x])){
						$file = $dbg[$x]['file'];
						$line = $dbg[$x]['line'];
						echo "<center><H3>". $file ." @ ". $line ."</H3></center>";
				}
		}
	
		if ($varDump) var_dump($vars);
		else print_r($vars);
		echo "</pre>";
	
		exit;
	}
}
/**
 * Validates a variable against a given type giving options for more advanced validation.
 *
 * @param mixed $var The variable to validate
 * @param string $type Can be int, str, float, double, bool, appending array will mean array of int, string etc.
 * @param mixed $arg1 The first argument to compare against, for numeric values this is a min. If this value is an array,
 * then its taken to mean that the variable must be a value in the array.
 * @param mixed $agr2 If applied will be the max value to compare against. For strings if both values are ints will take it
 * to mean the
 */
function cleanVar($var, $type = 'str', $arg1 = null, $arg2 = null){
	$checks = false;

	switch ($type){
		case 'str':
			//if (!is_a($var, 'String')) return null;
			
			if ( is_int($arg1) && ( strlen($var) < $arg1)) return null;
			if ( is_int($arg2) && ( strlen($var) > $arg2)) $var = trim($str, $arg2); 

                        //If its not an int then it could be a regex expression.
                        if ( !is_int($arg1) && !preg_match( $arg1, $var ) ) return null;
                        return $var;
			
		case 'float':
		case 'double':
		case 'int':
			if (!is_numeric($var)) return null;
			if (!is_null($arg1) && $var < $arg1) return null;
			if (!is_null($arg2) && $var > $arg2) return null;
			return (int) $var;
		case 'bool':
	}
}


/**
 * Convience function that passes a variable to cleanVar which validates a given value. This method is specific
 * to REQUEST vars where you pass the post field name instead of the var, the rest of the arguments follow
 * the same structure as cleanVar
 *
 * @param string $field Field name from the post which you want to clean
 * @param See Param lsit of cleanVar from the second argument on for the rest.
 * @return mixed Returns the variable cleaned or null if not loaded.
 */
function cleanREQUEST($field){
	if (!array_key_exists($field, $_REQUEST) ) return null;
	
	$args = func_get_args();

	$type = isset($args[1]) ? $args[1] : null;
	$arg1 = isset($args[2]) ? $args[2] : null;
	$arg2 = isset($args[3]) ? $args[3] : null;

	if (array_key_exists($field, $_REQUEST)){
		return cleanVar( $_REQUEST[$field], $type, $arg1, $arg2); 
	}
	
	return null;
	
}

/**
 * Convience function that passes a POST variable through cleanVar, and returns the results.
 * @param string $field Field name from the post which you want to clean
 * @param See Param lsit of cleanVar from the second argument on for the rest.
 * @return mixed Returns the variable cleaned or null if not loaded.
 */
function cleanPOST($field){
	if (!array_key_exists($field, $_POST) ) return null;
	
	$args = func_get_args();

	$type = isset($args[1]) ? $args[1] : null;
	$arg1 = isset($args[2]) ? $args[2] : null;
	$arg2 = isset($args[3]) ? $args[3] : null;

	if (array_key_exists($field, $_POST)){
		return cleanVar( $_POST[$field], $type, $arg1, $arg2); 
	}

	return null;

}

/**
 * Convience function that passes a GET variable through cleanVar, and returns the results.
 * @param string $field Field name from the post which you want to clean
 * @param See Param lsit of cleanVar from the second argument on for the rest.
 * @return mixed Returns the variable cleaned or null if not loaded.
 */
function cleanGET($field){
	if (!array_key_exists($field, $_GET) ) return null;
	
	$args = func_get_args();

	$type = isset($args[1]) ? $args[1] : null;
	$arg1 = isset($args[2]) ? $args[2] : null;
	$arg2 = isset($args[3]) ? $args[3] : null;

	if (array_key_exists($field, $_GET)){
		return cleanVar( $_GET[$field], $type, $arg1, $arg2); 
	}

	return null;

}

/**
 * Creats a string of random characters. 
 *
 * @param int $length The total length of the string.
 * @param bool $numbers Include Numbers.
 * @param bool $caseSensitive False will return all characters in upper case while true will create a mixture of upper and lowercase.
 * @return String of random characters.  
 */
function randomChars( $length, $numbers = true , $caseSensitive = true){
	$str = ''; 
	
	mt_srand();
	
	for ($x = 0; $x < $length; $x++ ){
		//Randomly select what type of character we want

		//Do we want to include numbers? 
		$y = $numbers ? 2 : 1;
		
		$charType = mt_rand(0, $y);
		
		switch ($charType){
			//Select a random character between A-Z [ASCII 65-90]
			case 0:
				$char = mt_rand(65, 90);
				break;
			//Select a random character between a-z [ASCII 97-122]
			case 1:
				$char = mt_rand(97, 122);
				break;
			//Select a random cahracter between 0-9 [ASCII 48-57]
			case 2:
				$char = mt_rand(48, 57);
				break;
		}
		
		$str .= chr( $char );
	}
	
	return ($caseSensitive) ? $str : strtoupper( $str );  
}

function simpleCleanError( $errNo, $errStr, $errFile, $errLine){
	$msg = '';
	$bgColor = 'yellow';
	
	switch ($errNo){
		case E_USER_WARNING:
			$msg = "<b>Warning : [$errNo] $errFile @ $errLine </b>\n\t$errStr";
			$bgColor = 'red';
			break;
		case E_USER_NOTICE:
			$msg = "<b>Notice : [$errNo] $errFile @ $errLine </b>\n\t$errStr";
			break;
		case E_USER_ERROR:
		default:
			$msg = "<b>Error : [$errNo] $errFile @ $errLine </b>\n\t$errStr";
			break;
	}
	
	//@TODO - have some sort of check here so that if we are in cli mode these errors are not handled like this.
	echo "<pre style='width:100%; border:thin solid black; background-color:$bgColor'>";
	echo "$msg";
	echo "</pre>";
	
	return true;
}

$tmp = set_error_handler ('simpleCleanError');

?>