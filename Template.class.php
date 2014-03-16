<?php
/**
 * An extended form of Savant3 used in particularly to support use of a wapper.
 * 
 * @author Shajinder Singh <ss@ss44.ca>
 * @created 10-Oct-2011
 */

namespace SQuick;

class Template{
	
	/**
	 * @var \SQuick\Template\Driver $_driver
	 */ 	
	protected $_driver = null;

	/**
	 * @var String name of the wrapper file to use.
	 */
	protected $_wrapper = null;
	
	/**
	 * @var String The path of where templates are stored.
	 */ 
	protected $_path = null;

	public function __construct( \SQuick\Template\Config $config = null ){
		
	dim( $config );

		// Hardcode the driver for now.
		$this->_driver = new Template\DriverSavant3();
	}

	/**
	 * Sets the wrapper file to use.
	 * 
	 * @param String Wrapper  
	 */
	public function setWrapper( $wrapperFile ){
		$this->_wrapper = $wrapperFile;
	}
	
	/**
	 * Returns the name/path of the current wrapper.
	 * @return String
	 */
	public function getWrapper(){
		//return ( !is_null( $this->_path ) ) ? $this->_path .'/'. $this->_wrapper : $this->_wrapper ;
		return $this->_wrapper;
	}

	/**
	 * Sets the home template path.
	 */
	public function setPath( $path ){
		$this->_path = realpath($path);
		
		if ( $this->_path === false){
			throw TemplateException::InvalidPath( $path );
		}

		$this->_driver->setPath( $this->_path );
	}

	public function addJS( $jsPath ){
		$this->_driver->addJS( $jsPath );
	}
	
	public function addCSS($cssPath ){
		$this->_driver->addCSS( $cssPath );

	}

	public function fetch( $tpl ){
		return $this->_driver->fetch( $tpl );
	}

	/**
	 * @override The parent display method to try and call the wrapper.
	 * 
	 * @param $tpl String File / Template to load.
	 * @param $includeWrapper Whether or not we want to include the wrapper with this display.
	 * @author $file File to attempt to load.
	 */
	public function display( $tpl = null, $includeWrapper = true ){
		
		if ( $this->_path ){
			// $tpl = $this->_path . '/'. $tpl;
		}

		try{
			if ( $this->getWrapper() && $includeWrapper){
				$value = $this->fetch( $tpl );
				$this->_content = $value;
				$this->_driver->display( $this->getWrapper() );
			}else{
				$this->_driver->display( $tpl );
			}
		}catch( Exception $e ){
			die( $e->getMessage() );
		}
	}

	public function __set( $key, $var ){
		$this->_driver->__set( $key, $var );
	}
	
 }
