<?php

/**
 * Scafollding class to deal with loading and routing.
 * 
 * @author Shajinder Singh <ss@ss44.ca>
 *
 */

namespace SQuick;

class Scaffolding{

	protected $_controllerPath = null;
	protected $_namespace = null;

	/**
	 * Adds a path for which to look for appropriate 
	 * controllers to route against.
	 */
	public function setControllerPath( $dirPath, $namespace = "\"" ){
		$this->_controllerPath = realpath($dirPath);
		$this->_namespace = $namespace;
	}

	/**
	 * Processes the url and selects the appropriate controller.
	 */
	public function process(){

		$path = $_SERVER['REQUEST_URI'];
		$pathArray = array_slice( explode( '/', $path ), 1);

		$pathArray = array_filter( $pathArray );
		
		// If we fail throw an error that no valid controller library file found. 
		if ( !$this->_triggerController( $pathArray ) ){
			throw new ScaffoldingException('No valid controller lib found.');			
		}


	}

	/**
	 * Searches a folder path to find if a valid controller exists.
	 * Rules for finding a controller:
	 *
	 *  - index.controller.class.php -> index
	 *	- index.controller.class -> $arg[0]
	 * 	- <$arg[0].controller.class.php -> $arg[1] | index
	 *  - <$arg[0]>/<$arg[1]>.controller.class.php
	 *  
	 * @param Mixed folder structure passed as a mixed array in sequence of folders / classes to look for.
	 * @param String Appends current contorllers schema with new folder depth.
	 * @param String Appends current namespace to the current name space.
	 * @return bool True if appropriate class was found and loaded. Otherwise return false.
	 */ 
	protected function _triggerController( $folderPath, $additionalPath = '', $additionalNS = ''){
		// If we find a parent class then load the appropriate class and try to show the appropriate 404.
		$possibleClass = null;

		// There is no folder path then only test we can do is look for an index.controller.php
		if ( empty($folderPath) ){
			
			// Test 1 - index.controller.class.php -> index
			$classFileName = "{$this->_controllerPath}/{$additionalPath}index.controller.class.php";
			$className = "$this->_namespace{$additionalNS}\index";			

			if ( file_exists( $classFileName ) ) {

				require_once( $classFileName );

				if ( method_exists( $className, 'index' ) ){			
					$class = new $className();
					$class->index();

					return true;
				}else{
					$possibleClass = $className;	
				}
			}

		}
		// We have a key so lets start our tests.
		else {
			
			// Test 2 - index.controller.class -> $arg[0]
			$classFileName = "{$this->_controllerPath}/{$additionalPath}index.controller.class.php";
			if ( file_exists( $classFileName ) ){
				
				require_once( $classFileName );
				$methodName = str_replace( array('-'), '_', $folderPath[0] );
				$className = "$this->_namespace{$additionalNS}\index";			

				if ( method_exists( $className, $methodName ) ){
					$class = new $className();
					$params = array_slice( $folderPath, 1);
					call_user_func( array($class, $methodName), $params );

					return true;
				}
				elseif ( class_exists( $className ) ){
					$possibleClass = $className;
				}
			}

			// Test 3 - <$arg[0].controller.class.php -> $arg[1] | index
			// Try and autoload
			$classFileName = "{$this->_controllerPath}/{$additionalPath}{$folderPath[0]}.controller.class.php";
			$className = "{$this->_namespace}{$additionalNS}\\{$folderPath[0]}";
			$methodName = array_key_exists(1, $folderPath) ? str_replace( array('-'), '_', $folderPath[1] ) : 'index';
			
			if ( file_exists( $classFileName ) ){

				// Try and load the appropriate controller class.
				require_once( $classFileName );

				// if there is second argument use that as the page 
				// otherwise look for an index.
				if ( method_exists($className, $methodName ) ){
					
					$class = new $className();
					$params = array_slice( $folderPath, 2);
					
					// Trigger appropriate class, method with paramaters.
					call_user_func( array($class, $methodName), $params );

					return true;
				}elseif ( class_exists($className ) ){ 
					$possibleClass = $className;
				}
			}

			// Test 4 - <$arg[0]>/<$arg[0]>.controller.class.php -> $arg[1]
			$classFileName = "{$this->_controllerPath}/{$additionalPath}{$folderPath[0]}/index.controller.class.php";

			if ( file_exists( $classFileName )  ){
				
				// Try and load the appropriate controller class.
				require_once( $classFileName );

				if ( method_exists( $className, $methodName ) ){
					return true;
				}elseif ( class_exists( $className ) ){
					$possibleClass = $className;
				}
			}

			// Test 3 - <$arg[0]>/<$arg[1]>.controller.class.php
			$dirPath = "{$this->_controllerPath}/{$additionalPath}{$folderPath[0]}";

			if ( is_dir( $dirPath ) ){
				$addToPath = array_shift($folderPath);
				$additionalPath = $additionalPath . $addToPath .'/';
				$addToNameSpace = '\\'.ucfirst(strtolower($addToPath));

				// Jump into rabbit hole and get recursive.
				return $this->_triggerController( $folderPath, $additionalPath, $addToNameSpace );

			}
		}

		// Couldn't find an appropriate method to display
		// show a 404.
		if ( is_subclass_of( $possibleClass, '\\SQuick\\Controller' ) ){
			$class = new $possibleClass();
			call_user_func( array($class, 'display404') );
			
			return true;
		}

		return false;

	}
}