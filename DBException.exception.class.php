<?php

namespace SQuick;

class DBException extends SQuick\Exception{
	public function __construct( $message = null){
		parent::__construct( $message );
	}
}