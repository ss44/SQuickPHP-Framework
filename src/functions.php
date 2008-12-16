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
function oops( $var, $showVarDump = false){
	global $args;
	
	//oops($args);
	
	echo (array_key_exists('SHELL', $_SERVER)) ? '' : '<pre>';
	$info = debug_backtrace();
	echo ((array_key_exists('SHELL', $_SERVER)) ? '' :"<h2>").$info[0]['file']." @ line ". $info[0]['line'] . ((array_key_exists('SHELL', $_SERVER)) ? "\n" :"</h2>");
	if ($showVarDump){
		var_dump($var);
	}else{
		print_r($var);
	};
	echo (array_key_exists('SHELL', $_SERVER)) ? '' :"</pre>";
}

function dim($var, $showVarDump = false){
	oops($var, $showVarDump); 
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
function qValidate($var, $type = 'str', $arg1 = null, $agr2 = null){

	switch ($type){
		case 'str':
			if (!is_a($var, 'String')) return false;
			
			
			break;
		case 'float':
		case 'double':
		case 'int':
		case 'bool':
	
	}
}
?>