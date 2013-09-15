<?php
/**
 * Abstract driver to control interaction with various template engines.
 */

namespace SQuick\Template;

abstract class Driver{

	public abstract function __set($key, $value);
	public abstract function fetch( $template );
	public abstract function display( $template );
	public abstract function setPath( $path );
	public abstract function addCSS( $path );
	public abstract function addJS( $path );

}
