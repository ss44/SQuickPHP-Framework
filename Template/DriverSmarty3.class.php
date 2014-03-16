<?php
/**
 * Savant3 template.
 */

namespace SQuick\Template;

class DriverSmarty3 extends \SQuick\Template\Driver{

	protected $_smarty = null;
	protected $_js = array();
	protected $_css = array();

	public function __construct(){
		// parent::construct( $config )
		$this->_smarty = new \Smarty();
		// $this->_savant->_js = array();
		// $this->_savant->_css = array();
	}

	public function __set( $key, $var ){
		$this->_smarty->assign( $key, $var );
	}

	public function fetch( $template ){
		
		$this->_smarty->assign('js', $this->_js);
		$this->_smarty->assign('css', $this->_css);

		return $this->_smarty->fetch( $template );
	}

	public function display( $template ){
		$this->_smarty->assign('js', $this->_js);
		$this->_smarty->assign('css', $this->_css);

		$result = $this->_smarty->display( $template );

	}

	public function setPath( $path ){
		$this->_smarty->setTemplateDir($path);	
	}

	public function addCSS( $path ){
		$this->_css[] = $path;
	}

	public function addJS( $path ){
		$this->_js[] = $path;
	}

	
}
