<?php

define('LOGIN_FAIL', 4);
define('USER_IS_FREE', 5);
define('USER_IS_PREMIUM', 6);
define('ERR_FILE_NO_EXIST', 114);
define('ERR_REQUIRED_PREMIUM', 115);
define('ERR_NOT_SUPPORT_TYPE', 116);
define('DOWNLOAD_ISQUERYAGAIN', 'isqueryagain');
define('DOWNLOAD_STATION_USER_AGENT', "Mozilla/4.0 (compatible; MSIE 6.1; Windows XP)");
define('DOWNLOAD_URL', 'downloadurl'); 				// will contain the real download url
define('DOWNLOAD_FILENAME', 'filename'); 			// Saved file name
define('DOWNLOAD_ISPARALLELDOWNLOAD', 'isparalleldownload');	//Task can download parallel flag.

class FreeWayFileHost {
	private $url, $user, $pass, $hostInfo, $cookie = "/tmp/freeWay.cookie";
	private $type = "premium";	

	public function __construct($url, $user, $pass, $hostInfo) {
		$this->url = $url;
		$this->user = $user;
		$this->pass = $pass;
		$this->hostInfo = $hostInfo;
	}
	
	public function GetDownloadInfo() {
		if(!file_exists($this->cookie))
		{
			$this->Verify(false);
		}

		$res = $this->Get("https://www.free-way.me/load.php?url=".urlencode($this->url)."&user=".$this->user."&pw=".$this->pass."&multiget=4", false, true);
		$downloadUrl = (string)$res;
		$name = $this->TryToGetFilename($downloadUrl);

		return array(
			    DOWNLOAD_URL => $downloadUrl,
			    DOWNLOAD_FILENAME => empty($name) ? NULL : $name,
			    DOWNLOAD_ISPARALLELDOWNLOAD => ($this->type == "premium") ? true : false,
			);
	}	

	public function TryToGetFilename($downloadUrl) 
	{
		$ch = curl_init($downloadUrl);
		curl_setopt ($ch, CURLOPT_HEADER, TRUE); 
		curl_setopt ($ch, CURLOPT_NOBODY, TRUE);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_BINARYTRANSFER, 0);	

		// limit range to save time (doesn't seem possible on netload)
		if (!strstr($downloadUrl, "netload.in/"))
		{
			curl_setopt ($ch, CURLOPT_RANGE, "0-100");
		}

		// do request and save response
		$response = curl_exec($ch);
		curl_close($ch);

		// build header array
		$header = array();
		$header_text = substr($response, 0, strpos($response, "\r\n\r\n"));
		foreach (explode("\r\n", $header_text) as $i => $line)
		{
			if ($i === 0)
			{
			    $header['http_code'] = $line;
			}
			else
			{
			    list ($key, $value) = explode(': ', $line);

			    $header[$key] = $value;
			}
		}
	
		// try to read-out filename from HTTP header
		if (!empty($header["Content-Disposition"]))
		{
			$nameParts = explode('=', $header["Content-Disposition"]);
			if ($nameParts[1]) 
			{
				$fileName = trim($nameParts[1],'";\' ');
		
				// remove utf-8 prefix if it exists
				if (substr($fileName, 0, 7 ) === "UTF-8''") 
				{
					$fileName = substr($fileName, 7);
				}
			
				return $fileName;
			}
		}

		return;
	}

	public function Verify($ClearCookie) {
		$loginURL = "https://www.free-way.me/ajax/jd.php?id=1&user=".urlencode($this->user)."&pass=".urlencode($this->pass);
		$res = $this->Get($loginURL,true);
	
		if($ClearCookie && file_exists($this->cookie))
			unlink($this->cookie);
				
		// validate that user can login with given credentials
		$loginValid = false;
		if ($res == "Valid login") {
			$loginValid = true;
		}

		// if login is valid, find out if user is free or premium user
		if ($loginValid) {
			$testURL = "https://www.free-way.me/ajax/jd.php?id=4&user=".urlencode($this->user)."&pass=".urlencode($this->pass);
			$testRes = (array) json_decode($this->Get($testURL,true));
			if ($testRes["premium"] == "Free")
			{
				$this->type = "free";
				return USER_IS_FREE;
			}
			else {
				$this->type = "premium";
				return USER_IS_PREMIUM;
			}
		}

		return LOGIN_FAIL;
	}
	
	private function Get($url, $getCookie=false, $getDownloadUrl=false) {
		$curl = curl_init();
		
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_USERAGENT, DOWNLOAD_STATION_USER_AGENT);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
				
		if ($getDownloadUrl) {
			curl_setopt($curl, CURLOPT_HEADER, true); 
		}
		else {
			curl_setopt($curl, CURLOPT_HEADER, false); 
		}
		
		if($getCookie)
			curl_setopt($curl, CURLOPT_COOKIEJAR, $this->cookie);
		else
			curl_setopt($curl, CURLOPT_COOKIEFILE, $this->cookie);
		
		$result = curl_exec($curl);		
		$statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
				
		if ($getDownloadUrl && $statusCode == 302)
		{
				preg_match_all('/^Location:(.*)$/mi', $result, $matches);
				if (!empty($matches[1]))
					$result = trim($matches[1][0]);
		}
		
		curl_close($curl);
		return $result;
	}
}

?>
