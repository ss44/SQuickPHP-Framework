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
		var_dump($this->host);
		$fp = fsockopen($this->host, 80);
		//$fp = fsockopen('shindasingh.com', 80);
		exit;
		return file_get_contents($this->uri);
	}
	
	protected function parseUri( $url ){

		preg_match('/(^https?:\/\/)?([a-z0-9\.\-\/]+)(\?.*)?$/i', $url, $temp);
		$protocol = isset($temp[1]) ? $temp[1] : 'http://';
		$path = isset($temp[2]) ? $temp[2] : '';
		$request = isset($temp[3]) ? $temp[3] : '';

		$this->host = $path;
	}
}
?>