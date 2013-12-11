<?php
 /**
  * SQLLite3 SQuickDB Driver for SQuickDB
  * 
  * @author ss <ss@ss44.ca>
  */

namespace SQuick\DB;

class DriverSqlite3 extends DBDriver{


 	public function getAll( SQuickQuery $q ){
		if (is_null($this->_dbObj)) 
			$this->connect();
		
		$r = $this->_dbObj->query( $q->getSelect() );

		if ($r) { 
			while( $row = $r->fetchArray( SQLITE3_ASSOC ) ){
				$result[] = $row;
			}

			return $result;	
		}

		throw new SQuickDBException( $this->_dbObj->lastErrorMsg() );
 	}

	public function getRow( SQuickQuery $q ){
		if (is_null($this->_dbObj))
			$this->connect();
		
		$r = $this->_dbObj->query( $q->getSelect() );
		$result = $r->fetchArray( SQLITE3_ASSOC );

		return $result;
	}

	public function getColumn( SQuickQuery $q ){
		if (is_null($this->_dbObj)) $this->connect();
		
		$r = $this->_dbObj->query( $q->getSelect() );
		
		if ($r->numColumns()) { 
			while( $row = $r->fetchArray( SQLITE3_NUM ) ){
				$result[] = $row[ 0 ];
			}
		}
		return $result;
	}

	public function getOne( SQuickQuery $q ){
		if (is_null($this->_dbObj))
			$this->connect();
		
		$r = $this->_dbObj->query( $q->getSelect() );
		
		if ($r->numColumns()) { 
			$row  = $r->fetchArray( SQLITE3_NUM );
			$result = array_pop($row);
		}

		return $result;


	}
	public function getAssoc( SQuickQuery $q ){
		if (is_null($this->_dbObj)) 
			$this->connect();
		
		$r = $this->_dbObj->query( $q->getSelect() );
		
		if ($r->numColumns()) { 
			while ($row = $r->fetchArray(SQLITE3_ASSOC)){
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
		$r = $this->_dbObj->query( $q->getSelect() );

		if ($r === false) { 
			throw new SQuickDBException( $this->_dbObj->lastErrorMsg() );	
		}

		return new SQuickDBResultSqlLite3( $r );

	}

	public function update( SQuickQuery $q ){
		if (is_null($this->_dbObj)) $this->connect();
		$result = $this->_dbObj->exec( $q->getUpdate() );
		return $result;

	}
	public function insert( SQuickQuery $q ){
		if (is_null($this->_dbObj)) $this->connect();
	
		$r = $this->_dbObj->exec( $q->getInsert() );
		
		if ($r === false){
			throw new SQuickDBException( $this->_dbObj->lastErrorMsg() );
		}
		$this->_lastID = $this->_dbObj->lastInsertRowID();
		return $this->_lastID;
	}

	public function delete( SQuickQuery $q ){
		if (is_null($this->_dbObj)) $this->connect();
		$result = $this->_dbObj->exec( $q->getDelete() );
		return $result;

	}
	public function upsert( SQuickQuery $q ){
		if (is_null($this->_dbObj)) $this->connect();
			$row = $this->getRow( $q );

		if (empty($row)){
			return $this->insert($q);
		}
		
		return $this->update( $q)
	}
	public function exec( SQuickQuery $q ){
		if (is_null($this->_dbObj)) $this->connect();
		@$result = $this->_dbObj->exec( $q->getQuery() );
		
		if ( !$result ){
			throw new SQuickDBException( $this->_dbObj->lastErrorMsg() );
		}

		return $result;

	}
	
	public function getTableStructure( $tableName ){
		$r = $this->_dbObj->query( 'PRAGMA table_info(\'' . $this->_dbObj->escapeString( $tableName ) . '\');' );
		
		while( $row = $r->fetchArray() ){
			$result[ $row['name'] ] = $row;
		}
		
		return $result;
	}

	public function connect( $config ){

	}
}