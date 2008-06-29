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
	echo '<pre>';
	if ($showVarDump){
		var_dump($var);
	}else{
		print_r($var);
	};
	echo "</pre>";
}
?>