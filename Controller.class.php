<?php

/**
 * A general controllers interface that can be extended.
 */
namespace SQuick;

abstract class Controller{

	// Handle the 404
	public function display404(){
		echo '<h1>404 File Not Found</h1>';
	}
}