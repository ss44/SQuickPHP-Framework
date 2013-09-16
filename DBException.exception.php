<?php

namespace SQuick;

class DBException extends Exception{
	public function __construct( $message = null){
		parent::__construct( $message );
	}
}