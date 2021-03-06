<?php
/**
 * Represents a SQuickDBResult iterator for SQLite
 */

class SQuickDBResultMySQL extends SQuickDBResult{

	protected $result = null;
	protected $counter = 0;
	protected $row = null;

	public function __construct( $result ){
		$this->result = $result;
		$this->next();
	}


	public function current (){
		
		if ( parent::getHelperObj() ) {
			$helper = $this->getHelperObj();
			$tmp = new $helper();
			$tmp->importSQuickDBResultRow( $this->row );
			return $tmp;
		}
		return $this->row;
	}

	public function key (){
		return $this->counter;
	}

	public function next (){
		$this->row = mysql_fetch_array( $this->result, MYSQL_ASSOC);

		if ( $this->row !== false )
			$this->counter++;

	}

	public function rewind(){
		return null;

	}

	public function valid(){
		return $this->row !== false;
	}
}