<?php
/**
 * Collection of commonly used functions to be included in our project.
 * @author Shajinder Singh <ss@ss44.ca>
 * @created 28-June-2008
 * @modified 29-May-2009
 */

/**
 * Outputs basic debug info with / pre tags attached.
 * @param mixed $var  The variable to output.
 * @param bool $showVarDump By default uses a print_r unless specified to use var_dump
 */

$tmp = register_shutdown_function(__NAMESPACE__.'\\SQuickCleanShutdown');;
$tmp = set_error_handler (__NAMESPACE__.'\\SQuickCleanError', E_ALL);
ini_set('display_errors', 0);

/**
 * Debug tools
 */
if (!function_exists('oops')){
	function oops( $vars, $varDump = false, $level = 0, $logFile = null){

		// If they have defined a log file always use that.
		$logFile = defined('SQUICK_LOG_FILE') ? SQUICK_LOG_FILE : $logFile;

		$dbg = debug_backtrace();

		$file = $dbg[0]['file'];
		$line = $dbg[0]['line'];

		$isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

		ob_start();

		if (PHP_SAPI == "cli" || $isAjax || $logFile){
			echo "\n-- $file @ $line --\n";
		}else{
			echo "<pre><H1>". $file ." @ ". $line ."</H1>";
		}

		for ($x = 1; $x < $level; $x++){
			if (isset($dbg[$x]) && isset($dbg[$x]['file'])){
				$file = $dbg[$x]['file'];
				$line = $dbg[$x]['line'];

				if (PHP_SAPI == "cli" || $isAjax || $logFile){
					echo "\t-- $file @ $line --\n";
				}else{
					echo "<center><H3>". $file ." @ ". $line ."</H3></center>";
				}
			}
		}

		if ($varDump) var_dump($vars);
		else print_r($vars);

		if (PHP_SAPI == "cli" || $isAjax || $logFile){
			echo "\n";
		}else{
			echo "</pre>";
		}

		if ($logFile){
			$contents = ob_get_contents();
			$fh = fopen( $logFile, 'a');
			fwrite($fh, $contents);
			fclose($fh);
		}else{
			flush(); ob_flush();
		}

		ob_end_clean();
	}
}
if (!function_exists('dim')){
	//Die improved merges oops with die
	function dim( $vars, $varDump = false, $level = 0, $logFile = null ){

		// If they have defined a log file always use that.
		$logFile = defined('SQUICK_LOG_FILE') ? SQUICK_LOG_FILE : $logFile;

		$dbg = debug_backtrace();

		$file = $dbg[0]['file'];
		$line = $dbg[0]['line'];

		$isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
		ob_start();

		if (PHP_SAPI == "cli" || $isAjax || $logFile){
			echo "\n-- $file @ $line --\n";
		}else{
			echo "<pre><H1>". $file ." @ ". $line ."</H1>";
		}

		for ($x = 1; $x < $level; $x++){
			if (isset($dbg[$x])){
				$file = $dbg[$x]['file'];
				$line = $dbg[$x]['line'];

				if (PHP_SAPI == "cli" || $isAjax || $logFile){
					echo "\t$file @ $line \n";
				}else{
					echo "<center><H3>". $file ." @ ". $line ."</H3></center>";
				}
			}
		}

		if ($varDump) var_dump($vars);
		else print_r($vars);

		if (PHP_SAPI == "cli" || $isAjax || $logFile){
			echo "\n";
		}else{
			echo "</pre>";
		}

		if ($logFile){
			$contents = ob_get_contents();
			$fh = fopen( $logFile, 'a');
			fwrite($fh, $contents);
			fclose($fh);
		}else{
			flush(); ob_flush();
		}

		ob_end_clean();
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

	if ( preg_match( '/(.*)\:array$/', $type, $tmp  ) ){
		// Cast the var as an array
		$var = (array) $var;

		foreach ( $var as &$cleaned ){
			$cleaned = cleanVar( $cleaned, $tmp[1], $arg1, $arg2 );
		}

		return $var;
	}

	switch ($type){
		case 'email':
			$arg1 = 'email';
		case 'str':
		case 'str:lower':
		case 'str:upper':
		case 'str:md5':
		case 'postalcode':

			if ($type == 'str:lower'){
				$var = strtolower( $var );
			}elseif( $type == "str:upper" ){
				$var = strtoupper( $var );
			}

			if ( is_int($arg1) && ( strlen($var) < $arg1)){
				 return null;
			}

			if ( is_int($arg2) && ( strlen($var) > $arg2)){
				$var = trim($var, $arg2);
			}

			if ( is_array($arg1) ){
				$var = in_array( $var, $arg1 ) ? $var : null;
				return $var;
			}

			//If the first argument is any of the following try to parse it based on rules.
			if (!is_null($arg1) && !is_int($arg1)){

				//Treat like a regex
				switch ($arg1){
					case 'date':
						$arg1 = '/^\d{1,2}[-\/\.]\s?\d{1,2}[-\/\.]\s?\d{2,4}\s?$/';
						break;
					case 'zipcode':
						$arg1 = '/^[0-9]{5}$/';
						break;
					case 'email':
						$arg1 = '/^[a-zA-Z][\w\.-]*[a-zA-Z0-9]@[a-zA-Z0-9][\w\.-]*[a-zA-Z0-9]\.[a-zA-Z][a-zA-Z\.]*[a-zA-Z]$/';
						break;
					case 'postalcode':
						$arg1 = '/[A-Z]\d[A-z][\s\-]?\d[A-Z]\d$/i';
						break;
				}

				$valid = preg_match( $arg1, $var );
				if (!$valid){
					return null;
				}
			}

			if ( $type == 'str:md5' ){
				return md5( $var );
			}

			return (string) $var;
		case 'date':
			$arg1 = '/^(\d{1,2})[-\/\.]\s?(\d{1,2})[-\/\.]\s?(\d{2,4})\s?$/';
			if (!preg_match($arg1, $var, $tmp)) return null;
			return mktime(0, 0, 0, $tmp[2], $tmp[1], $tmp[3]);

		case 'dec':
		case 'float':
		case 'double':
			if (!is_numeric($var)) return null;
			if (!is_null($arg1) && $var < $arg1) return null;
			if (!is_null($arg2) && $var > $arg2) return null;
			return (float) $var;

		case 'int':
			if (!is_numeric($var)) return null;
			if (!is_null($arg1) && $var < $arg1) return null;
			if (!is_null($arg2) && $var > $arg2) return null;
			return (int) $var;

		case 'bool':
			return (boolean) $var;
	}
}

function cleanArrayKey($key, $array){
	if (!array_key_exists( $key, $array )) return null;

	$args = func_get_args();
	$type = isset($args[2]) ? $args[2] : null;
	$arg1 = isset($args[3]) ? $args[3] : null;
	$arg2 = isset($args[4]) ? $args[4] : null;


	return cleanVar( $array[$key], $type, $arg1, $arg2);
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
	$vars[0] = $field;
	$vars[1] = $_REQUEST;

	$vars = array_merge( $vars, array_slice( $args, 1) );

	return call_user_func_array('cleanArrayKey', $vars );
}

/**
 * Convience function that passes a POST variable through cleanVar, and returns the results.
 * @param string $field Field name from the post which you want to clean
 * @param See Param lsit of cleanVar from the second argument on for the rest.
 * @return mixed Returns the variable cleaned or null if not loaded.
 */
function cleanPOST($field){
	$args = func_get_args();
	$vars[0] = $field;
	$vars[1] = $_POST;

	$vars = array_merge( $vars, array_slice( $args, 1) );

	return call_user_func_array('cleanArrayKey', $vars );
}

/**
 * Convience function that passes a GET variable through cleanVar, and returns the results.
 * @param string $field Field name from the post which you want to clean
 * @param See Param lsit of cleanVar from the second argument on for the rest.
 * @return mixed Returns the variable cleaned or null if not loaded.
 */
function cleanGET($field){

	$args = func_get_args();
	$vars[0] = $field;
	$vars[1] = $_GET;

	$vars = array_merge( $vars, array_slice( $args, 1) );

	return call_user_func_array('cleanArrayKey', $vars );

}

/**
 * Creats a string of random characters.
 *
 * @param int $length The total length of the string.
 * @param bool $numbers Include Numbers.
 * @param bool $caseSensitive False will return all characters in upper case while true will create a mixture of upper and lowercase.
 * @return String of random characters.
 */
function randomChars( $length, $numbers = true , $caseSensitive = true, $includeSymbols = false ){
	$str = '';

	mt_srand();

	for ($x = 0; $x < $length; $x++ ){
		//Randomly select what type of character we want

		//Do we want to include numbers?
		$y = 1;
		//001 - 1
		//011 - 3 include
		//101 - 5
		//111 - 7
		if ( $numbers ){
			$y = 2;
		}

		if ( $includeSymbols ){

		}
		//Lets make it a bit mask to know what type of characters we
		//are working with.

		$charType = mt_rand(0, $y);

		$chars = array();

		//Select a random character between A-Z [ASCII 65-90]
		$chars[] = mt_rand(65, 90);

		//Select a random character between a-z [ASCII 97-122]
		$chars[] = mt_rand(97, 122);

		//Select a random cahracter between 0-9 [ASCII 48-57]
		if ($numbers){
			$chars[] = mt_rand(48, 57);
		}

		if ( $includeSymbols ){
			$chars[] = mt_rand(32, 47);
		}

		//Get a random character from the chars array
		$str .= chr( $chars[ array_rand( $chars ) ] );
	}

	return ($caseSensitive) ? $str : strtoupper( $str );
}

function SQuickCleanError($errNo, $errStr, $errFile, $errLine){
	$msg = '';
	$bgColor = 'yellow';
	$errorLogFile = defined('SQUICK_ERROR_FILE') ? SQUICK_ERROR_FILE : null;
	ob_start();

	$dbg = debug_backtrace();

	switch ($errNo){
		case E_USER_WARNING:
			$msgHTML = "<b>Warning : [$errNo] $errFile @ $errLine </b>\n\t$errStr";
			$msgCLI = "* Warning : [$errNo] $errFile @ $errLine *\n\t$errStr";
			$bgColor = 'red';
			break;
		case E_USER_NOTICE:
			$msgHTML = "<b>Notice : [$errNo] $errFile @ $errLine </b>\n\t$errStr";
			$msgCLI = "* Notice : [$errNo] $errFile @ $errLine *\n\t$errStr";
			break;
		case E_USER_ERROR:
		default:
			$msgHTML = "<b>Error : [$errNo] $errFile @ $errLine </b>\n\t$errStr";
			$msgCLI = "* Error : [$errNo] $errFile @ $errLine *\n\t$errStr";
			break;
	}

	$isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');


	//@TODO - have some sort of check here so that if we are in cli mode these errors are not handled like this.
	if (PHP_SAPI == "cli" || $isAjax || $errorLogFile){
		echo "\n$msgCLI";
	}else{
		echo "<pre style='width:100%; border:thin solid black; background-color:$bgColor; color:black;'>";
		echo "$msgHTML";
	}



	if ( count($dbg > 1) ){
		echo "\n";
		for ($x = 1; $x < count ( $dbg); $x++){
			if ( isset($dbg[$x]['line']) && isset($dbg[$x]['file']) )
				echo "\t".$dbg[$x]['line']. ' @ '. $dbg[$x]['file']. "\n";
		}
	}

	if (PHP_SAPI == "cli" || $isAjax || $errorLogFile){
		echo "\n";
	}else{
		echo "</pre>";
	}

	if ($errorLogFile){
		$contents = ob_get_contents();
		$fh = fopen( $errorLogFile, 'a');
		fwrite($fh, $contents);
		fclose($fh);
	}else{
		flush(); ob_flush();
	}
	ob_end_clean();

	return true;
}

function SQuickCleanShutdown(){
	$error = error_get_last();
	$errorLogFile = defined('SQUICK_ERROR_FILE') ? SQUICK_ERROR_FILE : null;

	//If there wasn't an error then end normaly.
	if (is_null($error)){
		return;
	}
	ob_start();
	$isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

	if (PHP_SAPI == "cli" || $isAjax || $errorLogFile){
		echo "** Error **\n";
	}else{
		echo "<pre style='width:100%; border:thin solid black; background-color:pink; color:black;'>";
	}

	echo $error['message'];

	echo "\n";
	echo "\t".$error['line']. ' @ '. $error['file']. "\n";

	if (PHP_SAPI == "cli" || $isAjax || $errorLogFile){
		echo "\n **************** \n";
	}else{
		echo "</pre>";
	}

	if ($errorLogFile){
		$contents = ob_get_contents();
		$fh = fopen( $errorLogFile, 'a');
		fwrite($fh, $contents);
		fclose($fh);
	}else{
		flush(); ob_flush();
	}

	ob_end_clean();
}
/**
 * Displays the hedaer and footer.
 * @param <type> $page
 */
function callTemplateWrapper($temp = null){
	global $template;

	try{
		if (isset($_GET['overlay'])){
			$template->display( $temp );
		}else{
			$value = $template->fetch( $temp ) ;
			$template->content = $value;
			$template->display( 'wrapper.tpl.php' );
		}
	}catch( Exception $e ){
		die( $e->getMessage() );
	}
}

if (!function_exists('redirect')){

	/**
	 * Redirects the user and if there was output then displays a click here to continue link.
	 *
	 * @param String $url to redirect to.
	 */
	function redirect( $url ){

		header("Location: $url ");
		exit;
	}
}

/**
 * Loads a SQuick ini file. Which allows server specific variables, and combines these into
 * a single relevent section to be used.
 *
 * @param string $siteIni The site ini to use.
 * @return returns an array of the parsed config file.
 */
function loadSQuickIniFile( $siteIni, $setConstants = false ){

	$config = parse_ini_file( $siteIni, true );

	//If server is defined then get our server modes.
	$servers = array();
	$currentSite = array_key_exists( 'current_site', $config) ? $config['current_site'] : null;

	//If current site is null then no need to read the server variable.
	if (is_null($currentSite) && array_key_exists('SERVER', $config)){
		$servers = $config['SERVER'];
	}

	//Determine which instance the site is currently running.
	foreach ( $servers as $key=>$serverList ){
		foreach (explode(',', $serverList) as $server ){
			if (isset($_SERVER) && array_key_exists('SERVER_NAME', $_SERVER) ){
				$serverName = $_SERVER['SERVER_NAME'];
				$found = strpos( strtolower($server), strtolower($serverName) );
			}else{
				//If we're running in CLI mode then the above method won't work.
				//So we can determine which to run based on the hostname and path.
				$currentServerPath = php_uname("n").':'.getcwd();
				$found = strpos( strtolower($currentServerPath), strtolower($server) );
			}

			//If we found it then ue that key.
			if ( $found !== false){
				$currentSite = $key;
				break 2;
			}
		}
	}

	//If we have our current site
	if ($currentSite){

		$configCopy = $config;
		//Loop over the config and merge our server specific settings.
		foreach( $configCopy as $key=>$val ){
			if (preg_match('/^(.*)_'.$currentSite.'$/i', $key, $tmp)){
				// If we have a general catch all name already, then replace it's values with those of our newly found key.
				// By merging over the new values over the old ones.
				if ( array_key_exists( $tmp[1], $config) ){
					$config[ $tmp[1] ] = array_merge($config[ $tmp[1] ], $config[ $key ]);
				}
				// Otherwise just copy it as a whole.
				else{
					$config[ $tmp[1] ] = $config[ $key ];
				}
			}

			//Loop over all our keys in the config and make them lowercase
			if (is_array($val)){
				foreach ( $val as $tKey => $item ){
					$newKey = strtolower( $tKey );
					unset($config[$key][$tKey]);
					$config[$key][$newKey] = $item;

				}
			}else{
				$newKey = strtolower( $key );
				unset($config[$key]);
				$config[$newKey] = $value;
			}
		}
	}

	// Set the constants for all our variables in the form of SECTION_VAR_NAME
	if ( $setConstants ){
		foreach ( $config as $title => $vars ){
			foreach ( $vars as $sVarName => $sValue ){
				$cName = strtoupper( $title . '_' . $sVarName );
				if ( !defined($cName) ){
					define( $cName, $sValue );
				}
			}
		}
	}

	return $config;
}

/**
 * Tests to see if string is serialized.
 *
 * @param String $string The string to to test.
 * @return Boolean True if is a serialzied string false otherwise.
 */
function isSerialized( $string ){
	$isSerial = @unserialize( $string );
	return !($isSerial === false);
}

/**
 * Sets the autoloader that squick uses to load squick classes
 */
function SQuickAutoLoader( $class ){
	
	if ( substr( $class, 0, strlen('squick')) == "SQuick"){
		
		// If it looks like a namespace then remove that from the equation
		$classStr = explode('\\', $class);
			
		// If it breaks apart string then unset the first part
		if ( count( $classStr) > 1 ){
			
			unset( $classStr[0] );
			$class = join( '/', $classStr );
		}

		$filePaths = array(
			'exceptionFileName' => __DIR__.'/'.$class.'.exception.php',
			'classFileName' => __DIR__.'/'.$class.'.class.php',
			'interfaceFileName' => __DIR__.'/'.$class.'.interface.php',
		);
		
		foreach ( $filePaths as $path ){
			if ( file_exists( $path ) ){
				require_once( $path );
			}
		}
	}

}

spl_autoload_register(__NAMESPACE__.'\\SQuickAutoLoader');
