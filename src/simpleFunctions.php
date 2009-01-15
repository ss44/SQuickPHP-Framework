<?php
/**
 * Collection of commonly used functions to be included in our project.
 * @author Shajinder Padda <shajinder@gmail.com
 * @created 28-June-2008
 */

/**
 * Outputs basic debug info with / pre tags attached.
 * @param mixed $var  The variable to output.
 * @param bool $showVarDump By default uses a print_r unless specified to use var_dump
 */
/**
 * Debug tools
 */
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
			if (!is_a($var, 'String')) return null;
			break;
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

	$args = func_get_args();

	$type = isset($args[1]) ? $args[1] : null;
	$arg1 = isset($args[2]) ? $args[2] : null;
	$arg2 = isset($args[3]) ? $args[3] : null;

	if (array_key_exists($field, $_REQUEST)){
		return cleanVar( $_REQUEST[$field], $type, $arg1, $arg2);
	}

	return null;

}



?>