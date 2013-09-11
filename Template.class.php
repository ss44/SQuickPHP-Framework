<?php
/**
 * An extended form of Savant3 used in particularly to support use of a wapper.
 * 
 * @author Shajinder Singh <ss@ss44.ca>
 * @created 10-Oct-2011
 */

namespace SQuick;

require_once( dirname(__FILE__) .'/externals/Savant3.php');

class Template extends \Savant3{
 	

	/**
	 * @var String name of the wrapper file to use.
	 */
	protected $_wrapper = null;
	
	/**
	 * @var String The path of where templates are stored.
	 */ 
	protected $_path = null;

	/**
	 * @var Array The js path
	 */
	protected $_js = array();

	/**
	 * @var Array the css path
	 */
	protected $_css = array();


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
		return ( !is_null( $this->_path ) ) ? $this->_path .'/'. $this->_wrapper : $this->_wrapper ;
	}

	/**
	 * Sets the home template path.
	 */
	public function setPath( $path, $config ){
		$this->_path = realpath($path);
	}

	public function addJS( $jsPath ){
		$this->_js[] = $jsPath;
	}
	
	public function getJS(){
		return $this->_js;
	}

	public function getCSS(){
		return $this->_css;
	}

	public function addCSS($cssPath ){
		$this->_css[] = $cssPath;
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
			$tpl = $this->_path . '/'. $tpl;
		}

		try{
			if ( $this->getWrapper() && $includeWrapper){
				$value = $this->fetch( $tpl ) ;
				$this->_content = $value;
				parent::display( $this->getWrapper() );
			}else{
				parent::display( $tpl );
			}
		}catch( Exception $e ){
			die( $e->getMessage() );
		}
		
	}
	
 }
