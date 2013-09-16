<?php
/**
 * Template Exceptions
 */

namespace SQuick;

class TemplateException extends Exception{

	const INVALID_PATH = 100;

	public static function InvalidPath( $path ){
		return new self( "Specified template path {$path} does not exist.", self::INVALID_PATH );
	}
}