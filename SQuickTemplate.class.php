<?php
/**
 * An extended form of Savant3 used in particularly to support use of a wapper.
 * 
 * @author Shajinder Singh <ss@ss44.ca>
 * @created 10-Oct-2011
 */

require_once( dirname(__FILE__) .'/externals/Savant3.php');

class SQuickTemplate extends Savant3{
 	
	/**
	 * String name of the wrapper file to use.
	 */
	protected $_wrapper = null;
	
	/**
	 * Sets the wrapper file to use.
	 * 
	 * @param String Wrapper  
	 */
	public function setWrapper( $wrapperFile ){
		$this->_wrapper = $wrapperFile;
	}
	
	
	public function getWrapper(){
		return $this->_wrapper;
	}
	
	/**
	 * @override The parent display method to try and call the wrapper.
	 * 
	 * @param $tpl String File / Template to load.
	 * @param $includeWrapper Whether or not we want to include the wrapper with this display.
	 * @author $file File to attempt to load.
	 */
	public function display( $tpl = null, $includeWrapper = true ){
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
