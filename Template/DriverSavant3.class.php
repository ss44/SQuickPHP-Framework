<?php
/**
 * Savant3 template.
 */

namespace SQuick\Template;

class DriverSavant3 extends \SQuick\Template\Driver{

	protected $_savant = null;

	public function __construct(){
		// parent::construct( $config )
		$this->_savant = new \Savant3();
		$this->_savant->_js = array();
		$this->_savant->_css = array();
	}

	public function __set( $key, $var ){
		$this->_savant->$key = $var;
	}

	public function fetch( $template ){
		return $this->_savant->fetch( $template );
	}

	public function display( $template ){
		$result = $this->_savant->display( $template );

	}

	public function setPath( $path ){
		$this->_savant->addPath('template', $path);	
	}

	public function addCSS( $path ){
		$this->_savant->_css[] = $path;
	}

	public function addJS( $path ){
		$this->_savant->_js[] = $path;
	}

	public function setCachePath( $path ){}
}
