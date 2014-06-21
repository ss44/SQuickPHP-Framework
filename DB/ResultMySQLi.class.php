<?php
/**
 * Represents a SQuickDBResult iterator for SQLite
 */

namespace SQuick\DB;

class ResultMySQLi extends Result{

	protected $result = null;
	protected $counter = 0;
	protected $row = null;

	public function __construct( $result ){
		$this->result = $result;
		$this->next();
	}


	public function current(){

		if ( parent::getHelperObj() ) {
			$helper = $this->getHelperObj();
			$tmp = new $helper();
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