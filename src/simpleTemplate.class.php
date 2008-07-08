<?php
/**
 * An extension for Smarty, may not be needed but included, in the case that we do need to add custom functions to smarty.
 */

require_once('Smarty.class.php');

class SimpleTemplate extends Smarty{
	public $wrapper = '';

	function setWrapper( $wrapper ){
		
	}
}

?>