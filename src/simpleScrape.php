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
	protected $host = '';
	protected $port = 80;
	
	public function __construct( $uri, $method = "GET" ){
			$this->parseUri($uri);
			$this->method = $method;
	}
	
	public function setUserAgent( $userAgent ){
		
	}
		
	public function setOS( $os ){
		
	}
	
	public function getPage(){
		$fp = fsockopen($this->host, 80);
		//return file_get_contents($this->uri);
	}
	
	protected function parseUri( $uri ){
		oops($uri);
		preg_match('/(^https?:\/\/)?([a-z0-9\.\-\/])(\?.*)?$/i', $uri, $temp);
		oops($uri);
		oops($temp);
	}
}
?>