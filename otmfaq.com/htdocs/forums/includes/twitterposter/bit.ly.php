<?php

// ################################
// # This class is written by Milad @ http://services.milado.net/
// ################################

class BitLy
{
	var $Request = '';
	var $apiShortenURL= 'http://api.bit.ly/shorten?';
	var $login = '';
	var $apiKey = '';
	var $version = '2.0.1';
	var $format = 'xml';
	var $LongURLs = array();
	var $ServerResponseArray = '';
	var $ServerResponseXML = '';
	
	 function __construct($login, $apiKey)
	 {
		$this->login = $login;
		$this->apiKey = $apiKey;
	 }
	 
	 function AddURL($url)
	 {
		$this->LongURLs[] = $url;
	 }
	 
	 function PrepareRequest()
	 {
		$this->Request = $this->apiShortenURL .
			'login=' . $this->login .
			'&apiKey=' . $this->apiKey .
			'&version=' . $this->version .
			'&format=' . $this->format .
			$this->JoinLongURLs();
	 }
	 
	 function JoinLongURLs()
	 {
		if (count($this->LongURLs) == 1)
		{
			return '&longUrl=' . $this->LongURLs[0];
		}
		else if (count($this->LongURLs) > 1)
		{
			return '&longUrl=' . implode('&longUrl=', $this->LongURLs);
		}
	 }
	 
	 function Shorten()
	 {
		$this->PrepareRequest();
		
		$this->ServerResponseXML = @file_get_contents($this->Request);
		
		require_once(DIR . '/includes/class_xml.php');
		$parser = new vB_XML_Parser($this->ServerResponseXML);
		$this->ServerResponseArray = $parser->parse();
		
		if ($this->ServerResponseArray['errorCode'] == 0)
		{
			return $this->ServerResponseArray['results']['nodeKeyVal']['shortUrl'];
		}
		else
		{
			return false;
		}
	 }
}

?>