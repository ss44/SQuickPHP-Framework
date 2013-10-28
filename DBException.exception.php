<?php

namespace SQuick;

class DBException extends Exception{

	const INVALID_ENUM_KEY = 10001;
	const INVALID_FIELD = 10002;

	public function __construct( $message = null, $key = null ){
		parent::__construct( $message, $key );
	}

	public static function invalidEnumKey( $message ){
		return parent::__construct( $message, self::INVALID_ENUM_KEY );
	}

	public static function invalidField( $message ){
		parent::__construct( $message, self::INVALID_FIELD );
	}
}