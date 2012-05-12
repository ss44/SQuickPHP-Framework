<?php
/**
 * Extends exception to enable framework specific specific exception handling.
 *
 * @author Shajinder Padda <ss@ss44.ca>
 * @created 30-Jan-2011
 */

class SQuickException extends Exception{
 	
 	const MISSING_PARAMS = 1000;
 	const INVALID_PARAMS = 1001;

 	public function __construct( $errorMessage = "", $code = null){
 		parent::__construct( $errorMessage, $code );
 	}

	public static function missingParam( $message ){
		return new SQuickException( $message, self::MISSING_PARAMS );
	}

 	public static function invalidParam( $message ){
 		return new SQuickException( $message, self::INVALID_PARAMS );
 	}


}