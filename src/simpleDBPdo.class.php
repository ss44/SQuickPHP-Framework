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
}

?>