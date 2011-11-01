<?php
 /** 
  * A simple cms class that stores fields.
  * to a database, and allows easy retrival.
  *
  * It is expected that this class is extended.
  * by the sub sites.
  * 
  * @aStor Shajinder Padda <ss@ss44.ca>
  * @created 20-Oct-2011
  */

  class SimpleCMS extends SimpleDB{
  	
    protected $fields = array();
  	protected $section = null;
  	protected $errors = array();

  	
 	  protected __construct(  ){
  		super();
  	}

  	public load( $id ){
  		
  	}

  	public save(){
  		
  	}


  	public static getFields( $section, $limit, $start ){
  		
  	}

  	public static getCount( $section ){
  		
  	}
 }