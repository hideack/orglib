<?php
require_once("HTTP/Client.php");

/**
 * Naivebayes.php 
 *
*  @category  Algorithm 
 * @package   Nabaztag 
 * @author    hideack <author@mail.com>
 * @license   http://www.php.net/license/3_01.txt The PHP License, version 3.01
 * @version   0.1 
 * @link       
 * @see       
 */
class Nabaztag
{
	private $serial;
	private $token;
	private $httpclient;
	
	// --- Nabaztag Web APIの位置
	private $api;
	private $streamapi;

	function __set($name, $value){
		switch($name){
			case "serial":	$this->serial = $value;	break;
			case "token":	$this->token  = $value;	break;
			default:
				break;
		}
	}

	public function __construct(){
		$this->httpclient =& new Http_Client();
		$this->api       = "http://api.nabaztag.com/vl/FR/api.jsp?sn=%s&token=%s&";
		$this->streamapi = "http://api.nabaztag.com/vl/FR/api_stream.jsp?sn=%s&token=%s&";
	}

	public function say($message){
		$message = urlencode($message);

		$command = sprintf(
			$this->api."voice=JP-Tamura&tts=%s",
			$this->serial,
			$this->token,
			$message
		);

		$this->httpclient->get($command);
	}

	public function playMp3($list){
		$command = sprintf(
			$this->streamapi."urlList=%s",
			$this->serial,
			$this->token,
			$list
		);

		$this->httpclient->get($command);
	}
}

$alfons = new Nabaztag();
$alfons->serial = "***********";
$alfons->token  = "**********";
$alfons->say("テスト");

?>
