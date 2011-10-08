<?php
/**
 * An extended form of Savant3 used in particularly to support use of a wapper.
 * 
 * @author Shajinder Singh <ss@ss44.ca>
 * @created 10-Oct-2011
 */

 if (!class_exists('Savant3')){
 	throw new SimpleException("Cannot use SimpleTemplateSavant3 without first including the Savant3 template engine.", 1);
 }
 
 class SimpleTemplateSavant3 extends Savant3{
 	
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
	 * @author $file File to attempt to load.
	 */
	public function display( $tpl = null ){
		
		
		try{
			if ( $this->getWrapper() ){
				$value = $this->fetch( $tpl ) ;
				$this->_content = $value;
				parent::display( $this->getWrapper() );
			}
		}catch( Exception $e ){
			die( $e->getMessage() );
		}
		
	}
	
 }
