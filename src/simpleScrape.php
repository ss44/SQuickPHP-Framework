<?php
/**
 * Class designed for using php to fake headers
 * and mimic a webbrowser. Ideally used to scrape a website.
 * 
 * @author Shajinder Padda <shajinder@pause.ca>
 * @created 27-July-2008
 */

class simpleScrape{
	
	protected $uri = '';
	protected $method = '';
	
	public function __construct( $uri, $method = "GET" ){
			$this->uri = $uri;
			$this->method = $method;
	}
	
	public function setUserAgent( $userAgent ){
		
	}
	
	public function setOS( $os ){
		
	}
	
	public function getPage(){
		return file_get_contents($this->uri);
	}
}
?>