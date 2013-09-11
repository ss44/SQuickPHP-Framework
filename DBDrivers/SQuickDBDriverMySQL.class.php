<?php
/**
 * mysql database driver
 * 
 * @author ss <ss@ss44.ca>
 * @created 13-Oct-2012
 */

namespace SQuick\DB\

class MySQL extends DBDriver{
		
	public function getAll( SQuickQuery $q ){
		$this->checkConnection();
		$r = mysql_query($q->getSelect(), $this->connection);

		if ($r === false ){
			throw new SQuickDBException( "Unable to perform query: " . mysql_error() );
		}

		if ($r !== false && mysql_num_rows($r) > 0){
			while($row = mysql_fetch_assoc($r)){
				$result[] = $row;
			}
			return $result;
		}
	}
	public function getRow( SQuickQuery $q ){
		$this->checkConnection();

		$r = mysql_query($q->getSelect(), $this->connection);

		if ($r === false || is_null($r) ) {
			throw new SQuickDBException( "Unable to perform query: " . mysql_error() );
		}
		
		$result = null;

		if (@mysql_num_rows($r) > 0){
			$result = mysql_fetch_assoc( $r );
		}

		return $result;
	}

	public function getColumn( SQuickQuery $q ){
		$this->checkConnection();

		$r = mysql_query($q->getSelect(), $this->connection);

		if (mysql_num_rows($r) > 0){
			while ($row = mysql_fetch_assoc($r)){
				$result[] = $row[ $column ];
			}
		}
		return $result;
	}

	public function getOne( SQuickQuery $q ){
		$this->checkConnection();

		$r = mysql_query($q->getSelect(), $this->connection);

		if (mysql_num_rows($r) > 0){
			$row = mysql_fetch_assoc($r);
			$result = array_pop( $row );
		}

		return $result;
	}

	public function getAssoc( SQuickQuery $q ){
		$this->checkConnection();

		$r = mysql_query($q->getSelect(), $this->connection);

		if (mysql_num_rows($r) > 0){
			while( $row = mysql_fetch_assoc($r)){
				if (array_key_exists($key, $row)){
					$result[ $row[$key] ] = $value && array_key_exists($value, $row) ? $row[$value] : $row;
				}else{
					throw new SQuickDBException('Invalid key. Not found in result.');
				}
			}
			return $result;
		}
	}

	public function getResult( SQuickQuery $q ){
		$this->checkConnection();
		$r = mysql_query($q->getSelect(), $this->connection);

		if ($r === false ){
			throw new SQuickDBException( "Unable to perform query: " . mysql_error() );
		}

		return new SQuickDBResultMySQL( $r );
	}

	public function update( SQuickQuery $q ){
		$this->checkConnection();

		$result = mysql_query($q->getUpdate(), $this->connection);
		return $result;
	}

	public function insert( SQuickQuery $q ){
		$this->checkConnection();

		$result = mysql_query($q->getInsert(), $this->connection);
		
		if (!$result){
			throw new SQuickDBException( mysql_error( $this->connection ));
		}
		
		$this->_lastID = mysql_insert_id($this->connection);
		return $this->_lastID;
	}

	public function delete( SQuickQuery $q ){
		$this->checkConnection();
		$result = mysql_query( $q->getDelete(), $this->connection);
		return $result;
	}

	public function upsert( SQuickQuery $q ){
		$this->checkConnection();

		try{
			$row = $this->getRow( $q );

			if (empty($row)){
				return $this->insert($q);
			}
			
			//Otherwise update.
			return $this->update($q);
		}
		//Throw errors up.
		catch( Exception $ex ){
			throw $ex;
		}
	}

	public function exec( SQuickQuery $q ){
		$this->checkConnection();
		$result = mysql_query( $q->getQuery(), $this->connection);

		if ( $result === false)
			throw new SQuickDBException( mysql_error( $this->connection ) ) ;

		return $result;
	}

	public function getTableStructure( $tableName ){
		$this->checkConnection();
		$r = mysql_query('Describe '.mysql_real_escape_string($tableName), $this->connection);
		
		$result = null;
		while ($r == true && $row = mysql_fetch_assoc($r) ) {
			$result[ $row['Field'] ] = $row;
		}
		
		return $result;
	}

	public function connect(){
		$this->connection = mysql_connect($this->config->path, $this->config->user, $this->config->pass);
		mysql_select_db($this->config->name, $this->connection);

		if (!$this->connection) 
			throw new SQuickDBException("Unable to connect to DB.");

	}

	protected function checkConnection(){
		if (is_null($this->connection)) 
			$this->connect();
	}

}