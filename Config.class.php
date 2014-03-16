<?php
/**
 * SQuick master config loader that stores all config options from setting files
 */

namespace SQuick;

class Config{
	
	protected static $instances = array();

	public static function loadConfig( $configFile = null ){

		if ( is_null( $configFile ) && defined( 'SQUICK_INI_FILE' ) ){
			$configFile = SQUICK_INI_FILE;
		}
		
		$configFile = realpath( $configFile );
		
		if ( !array_key_exists( $configFile, self::$instances ) ){
			self::$instances[ $configFile ] = loadSQuickIniFile( $configFile );
		}

		return self::$instances[ $configFile ];
	}
}
