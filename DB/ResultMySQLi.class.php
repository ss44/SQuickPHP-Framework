<?php
/**
 * Represents a SQuickDBResult iterator for SQLite
 */

namespace SQuick\DB;

class ResultMySQLi extends Result{

	protected $counter = 0;
	protected $row = null;

	public function __construct( $db, $result ){
		parent::__construct( $db, $result );		
		$this->next();
	}

	public function escape( $var ){
		return $ths->connection->escape_string( $var );
	}

	public function current(){

		if ( parent::getHelperObj() ) {
			$helper = $this->getHelperObj();
			
			$tmp = new $helper();
			$tmp->useDB( $this->_db );

			$tmp->importSQuickDBResultRow( $this->row );
			return $tmp;
		}

		return $this->row;
	}

	public function key (){
		if ( $this->_keyField && parent::getHelperObj() ){
			$obj = $this->current();
			return $obj->{$this->_keyField};
		}

		return $this->counter;
	}

	public function next (){
		$this->row = $this->result->fetch_assoc();

		if ( !is_null($this->row) ){
			$this->counter++;
		}
	}

	public function rewind(){
		return null;

	}

	public function valid(){
		return !is_null($this->row);
	}
}