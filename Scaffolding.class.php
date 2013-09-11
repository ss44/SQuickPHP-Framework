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

		if ( empty($pathArray) || !$pathArray[0] ){
			$pathArray[0] = 'index';
		}

		$className = strtolower(str_replace(array(' ', '-',), '_', $pathArray[0]));

		$filePath = $this->_controllerPath.'/'.strtolower($className.'.controller.class.php');

		// Load the appropriate class.
		if ( file_exists( $filePath ) ){
			
			require_once( $filePath );
			$className = $this->_namespace.'\\'.$className;

			if ( class_exists($className) ){
				if ( array_key_exists(1, $pathArray ) && !is_null( $pathArray[1] ) && !empty($pathArray[1])  ){
					
					$methodName = strtolower( $pathArray[1] );
					
					// Check if function exists
					if ( method_exists( $className,  $methodName ) ){
						$vars = array_splice($pathArray, 2);
						$class = new $className();
						call_user_func_array( array( $class, $methodName ), $vars );
						exit;
					}
				}else{
					$class = new $className();
					$class->index();
					exit;
				}
			}
		}

		throw new ScaffoldingException('No valid controller lib found.');
	}

}