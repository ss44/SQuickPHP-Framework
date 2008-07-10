<?php
/**
 * A custom db abstraction class that for now will extend pdo, however
 * in the future, may need to break off into its own class in the case
 * where pdo can not be supported. 
 *
 * @author Shajinder Padda
 * @created 1-July-2008
 */

class simpleDBPdo extends PDO{
	
	/**
	 * Returns all records from query as an array.
	 *
	 * @param string|simpleQuery $query 
	 * @return mixed Array of all matching results.
	 */
	public function getAll($query){
		$sql = is_string($query) ? $query : $query->getSelect();
		
		$statement = $this->query($sql);
		return $statement->fetchAll();
	}
	
	/**
	 * Loops through all results and returns results as an associtive array based on the $field, $value
	 * pair. If no value is given then returns all results.
	 *
	 * @param simpleQuery | string  $query
	 * @param string $field
	 * @param string $value 
	 * @return array | mixed
	 * 
	 */
	public function getAssoc($query, $field, $value = ''){
		//Get all records 
		$results = $this->getAll($query);
		$assocArray = array();
		
		//Loop through the records and reindex
		foreach ($results as $result){
			if (isset($result[ $field ] )){
				if ($value && isset($result[ $value ])) $assocArray[ $result[$field] ] = $result[$value];
				else $assocArray[ $result[$field] ][] = $result;
			}
		}
		
		return $assocArray;
	}
	/**
	 * Returns a single row
	 *	TODO: error checking
	 * @param string|simpleQuery $query
	 */
	public function getRow($query){
		$sql = is_string($query) ? $query : $query->getSelect();
		//oops($sql);
		$statement = $this->query( $sql );
		return $statement->fetch();
	}
}

?>