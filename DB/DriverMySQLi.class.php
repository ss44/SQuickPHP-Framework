<?php
/**
 * mysql database driver
 * 
 * @author ss <ss@ss44.ca>
 * @created 13-Oct-2012
 */

namespace SQuick\DB;

class DriverMySQLi extends Driver{
		
	public function getAll( \SQuick\Query $q ){
		$this->checkConnection();
		$r = $this->exec( $q->getSelect() );

		if ($r !== false && $r->num_rows > 0){
			
			while( $row = $r->fetch_assoc() ){
				$result[] = $row;
			}

			return $result;
		}
	}
	public function getRow( \SQuick\Query $q ){
		$this->checkConnection();

		$r = $this->exec( $q->getSelect() );

		if ( $r->num_rows > 0 ){
			$result = $r->fetch_assoc();
		}

		return $result;
	}

	public function getColumn( \SQuick\Query $q, $column ){
		$this->checkConnection();

		$r = $this->exec( $q->getSelect() );

		$result = array();

		if ( $r->num_rows > 0){
			while ($row = $r->fetch_assoc() ){
				$result[] = $row[ $column ];
			}
		}

		return $result;
	}

	public function getOne( \SQuick\Query $q ){
		$this->checkConnection();

		$r = $this->exec( $q->getSelect() );

		if ( $r->num_rows > 0){
			$row = $r->fetch_assoc();
			$result = array_pop( $row );
		}

		return $result;
	}

	public function getAssoc( \SQuick\Query $q, $key, $value = null ){
		$this->checkConnection();

		$r = $this->exec( $q->getSelect() );

		if ( $r->num_rows > 0){
			while( $row = $r->fetch_assoc() ){
				if (array_key_exists($key, $row)){
					
					if ( is_null( $value ) ){
						$result[ $row[$key] ][] = $row;	
					}
					elseif ( array_key_exists( $value, $row ) ){
						$result[ $row[$key] ] = $row[$value];	
					}else{
						throw new \SQuick\DBException("Invalid value - $value. Not part of returned results");
					}
					
				}else{
					throw new \SQuick\DBException('Invalid key. Not found in result.');
				}
			}
			return $result;
		}
	}

	public function getResult( \SQuick\Query $q ){
		$this->checkConnection();
		$r = $this->exec( $q->getSelect() );

		return new ResultMySQLi( $r );
	}

	public function update( \SQuick\Query $q ){
		$this->checkConnection();

		$result = mysql_query($q->getUpdate(), $this->connection);
		return $result;
	}

	public function insert( \SQuick\Query $q ){
		$this->checkConnection();

		$result = $this->exec( $q->getInsert() );
		
		$this->_lastID = $this->connection->insert_id;
		
		return $this->_lastID;
	}

	public function delete( \SQuick\Query $q ){
		$this->checkConnection();
		$result = $this->connection->query( $q->getDelete() );
		return $result;
	}

	public function upsert( \SQuick\Query $q ){
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

	public function exec( $q ){
		$this->checkConnection();

		if ( is_a($q, '\SQuick\Query') ){
			$q = $q->getQuery();
		}

		$result = $this->connection->query( $q );

		if ( $result === false ){
			throw new \SQuick\DBException( $this->connection->error ) ;
		}

		return $result;
	}

	public function getTableStructure( $tableName ){
		$this->checkConnection();
		$r = $this->exec( new \SQuick\Query('Describe '.$this->connection->escape_string($tableName) ) );
		
		$result = null;

		while ($r == true && $row = $r->fetch_assoc() ) {
			$result[ $row['Field'] ] = $row;
		}
		
		return $result;
	}

	public function connect(){
		$this->connection = new \mysqli( $this->config->path, $this->config->user, $this->config->password, $this->config->name );

		//mysql_connect($this->config->path, $this->config->user, $this->config->password );
		// mysql_select_db($this->config->name, $this->connection);

		if ($this->connection->connect_error){
			throw new \SQuick\DBException("Unable to connect to DB. [Error " . $this->connection->connect_error . "]");
		}

	}

	protected function checkConnection(){
		if (is_null($this->connection)){
			$this->connect();
		}
	}

	/**
	 * Parses an enum value from a given field.
	 * @param String 
	 */
	public function parseEnum( $fieldInfo ){

		if ( !array_key_exists( 'Type', $fieldInfo ) ){
			throw DBException::invalidEnumKey('Unable to process ENUM field. Missing Type definition.');
		}

		if ( !preg_match('/^enum\((.*)\)?\)$/', $fieldInfo['Type'], $tmp ) ) {
			throw DBException::invalidEnumKey('Field does not appear to be of type ENUM');
		}

		$csv = $tmp[1];
		$values = str_getcsv( $csv, ',', '\'' );


		// Appropriate key index for mysql starts at 1 so up the keys by 1.
		$fixedKeyArray = array();
		foreach ( $values as $key => $value ){
			$fixedKeyArray[ $key + 1 ] = $value;
		}

		return $fixedKeyArray;

	}
}