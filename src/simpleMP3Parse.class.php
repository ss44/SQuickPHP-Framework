<?php

/**
 * Extends simpleFileParse but deals with MP3's specifically, and loads,
 * id3 tag information for each file.
 *
 * @author Shajinder Padda <shajinder@gmail.com
 * @created  17-July-2009
 */
require_once('simpleFileParse.class.php');
require_once(dirname(__FILE__).'/getid3/getid3.php');

class simpleMP3Parse extends simpleFileParse{
	
	private $id3 = null;
	
	//call the parent process
	public function __construct($rootFolder = '.', $folder = null){
		parent::__construct($rootFolder, $folder);
		$this->_mode = 'advanced';
		$this->includeExt('mp3');
		
		$this->id3 = new getID3;
	}
	
	protected function filter_file_MP3( $file ){
		return $this->id3->analyze($file);
	}
	
}

?> 