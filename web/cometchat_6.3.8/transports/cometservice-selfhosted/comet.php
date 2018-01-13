<?php
include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."config.php");
class Comet{
	public $requestUrl;
	public $domainName;
	public $domainKey;
	public $retries = 3;
	private $source = "phpp";
	private $invalidResponseMessage = "Invalid response received from server.";
	function __construct($keya,$keyb){
		$domainKey="11111111-1111-1111-1111-111111111111";
		$this->requestUrl = CS_TEXTCHAT_SERVER."/cometservice.ashx";
		$this->domainName = Comet::sanitizeDomainName($_SERVER['HTTP_REFERER']);
		$this->domainKey = $domainKey;
	}
	private static function sanitizeDomainName($domainName){
		if (strpos($domainName, "http://") === 0 || strpos($domainName, "https://") === 0){
			return $domainName;
		}
		return "http://" . $domainName;
	}
	private static function addQueryToUrl($url, $key, $value = null){
		if (empty($key)){
			return $url;
		}
		return $url . (substr_count($url, "?") > 0 ? "&" : "?") . urlencode($key) . "=" . urlencode($value);
	}
	public function processChannel1($channel){
		$channel = '/'. ltrim($channel,'/');
		return $channel;
	}
	public function publish($publications){
		if(!empty($publications['message'])){
			$publications['data']=$publications['message'];
			unset($publications['message']);
		}
		if (!empty($publications['channel'])){
			$publications['channel'] = '/'. ltrim($publications['channel'],'/');
			$publications = array($publications);
		}
		return $this->send($publications);
	}
	private function send($publications){
		$writeContent = Comet::toJson($publications);
		$url = $this->requestUrl;
		$url = Comet::addQueryToUrl($url, "key", $this->domainKey);
		$url = Comet::addQueryToUrl($url, "src", $this->source);
		$response = Comet::post($url, $writeContent, "application/json", $this->domainName);
		if (empty($response)){
			foreach ($publications as &$publication)
			{
				$publication["successful"] = true;
				$publication["timestamp"] = date("Y-m-d") . "T" . date("H:i:s") . ".00";
			}
		}else{
			$publications = Comet::fromJson($response);
		}
		return $publications;
	}
	private static function post($url, $data, $contentType, $referrer){
		$referrer = Comet::sanitizeDomainName($referrer);
		$urlParts = parse_url($url);
	     if (!function_exists('curl_init')){
			$options = array(
				"http" => array(
					"method" => "POST",
					"content" => $data,
					"protocol_version" => "1.0",
					"header" =>
						"Content-Type: " . $contentType . "\r\n" .
						"Referer: " . $referrer . "\r\n" .
						"Host: " . $urlParts["host"] . "\r\n"
				)
			);
			$context = stream_context_create($options);
			$pointer = @fopen($url, "r", false, $context);
			if (!$pointer){
				throw new Exception("Problem writing data to $url, $php_errormsg");
			}
			$response = @stream_get_contents($pointer);
			if ($response === false){
				throw new Exception("Problem reading data from $url, $php_errormsg");
			}
		}else{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: '.$contentType, 'Referer: ' . $referrer, 'Host: ' . $urlParts["host"]));
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			$response = curl_exec ($ch);
			curl_close($ch);
		}
		return $response;
	}
	private static function toJson($publications){
		$publicationJsons = array();
		foreach ($publications as $publication){
			$publicationJson = str_replace('\/', '/', json_encode($publication));
			$publicationJsons[] = $publicationJson;
		}
		return "[" . implode($publicationJsons, ",") . "]";
	}
	private static function fromJson($publicationsJson){
		if (get_magic_quotes_gpc()){
			$publicationsJson = stripslashes($publicationsJson);
		}
		return json_decode($publicationsJson, true);
	}
}

?>
