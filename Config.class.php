<?php
/**
 * SQuick master config loader that stores all config options from setting files
 */

namespace SQuick;

class Config{
	
	protected static $instances = array();

	protected static $configCaches = array();

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

	public static function getConfig( $configSection, $classToUse, $configFile = null ){

		$instances = self::loadConfig( $configFile );

		$configSection = strtolower($configSection);
		
		$configObj = null;

		// Check our cache to see if this config was loaded already if not attempt to load it.
		if ( !array_key_exists( $configFile, self::$configCaches ) || !array_key_exists( $classToUse, self::$configCaches[ $configFile ] ) ){
			// @todo 
			// Throw an exception if no appropriate config section works.
			if ( !array_key_exists( $configSection, $instances ) ){
				throw new ConfigException("Config section $configSection not set.");
			}

			$section = $instances[ $configSection ];

			if ( is_string( $classToUse ) ){
				$configObj = new $classToUse();
			}

			foreach ( $section as $field => $value ){
				// @todo Test that the config class implements our SQuickConfig functions.
				$configObj->setConfig( $field, $value );
			}

			self::$configCaches[ $configFile ][ $configSection ] = $configObj;
		}

		return self::$configCaches[ $configFile ][ $configSection ];
	}
}