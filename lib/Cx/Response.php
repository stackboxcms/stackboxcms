<?php
/**
 * HTTP Response Class
 * $Id$
 *
 * Handles HTTP response, sending response and headers to the browser
 *
 * @package Cx Framework
 * @author Vance Lucas <vance@vancelucas.com>
 * @link http://cont-xt.com/
 *
 * @version			$Revision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$Date$
 */
class Cx_Response
{
	public $status = 200;
	public $content = "";
	public $encoding = "UTF-8";
	public $contentType = "text/html";
	public $protocol = "HTTP/1.1";
	public $headers = array();
	protected $router;

	public function __construct($status = 200, $content = "")
	{
		$this->status = $status;
		$this->protocol = $_SERVER['SERVER_PROTOCOL'];
		$this->content = $content;
	}

	public function addHeader($key, $value)
	{
		// normalize headers ... not really needed
		for ($tmp = explode("-", $key), $i=0;$i<count($tmp);$i++) {
			$tmp[$i] = ucfirst($tmp[$i]);
		}
		
		$key = implode("-", $tmp);
		if ($key == 'Content-Type') {
			if (preg_match('/^(.*);\w*charset\w*=\w*(.*)/', $value, $matches)) {
				$this->contentType = $matches[1];
				$this->encoding = $matches[2];
			} else {
				$this->contentType = $value;
			}
		} else {
			$this->headers[$key] = $value;
		}
	}

	public function status($status)
	{
		$this->status = $status;
	}

	public function setEncoding($encoding)
	{
		$this->encoding = $encoding;
	}

	public function appendContent($content)
	{
		$this->content .= $content;
	}

	public function content($content)
	{
		$this->content = $content;
	}

	public function setContentType($contentType)
	{
		$this->contentType = $contentType;
	}
  
	public function getContentType()
	{
		return $this->contentType;
	}

	
	public function setRouter($router)
	{
		$this->router = $router;
	}
	
	// Clear any previously set headers
	public function clearHeaders()
	{
		$this->headers = array();
	}
	
	// Clear any previously set redirects
	public function clearRedirects()
	{
		if(isset($this->headers['Location'])) {
			unset($this->headers['Location']);
		}
		return true;
	}
	
	// Check to see if any redirects have been set so far
	public function hasRedirects()
	{
		return isset($this->headers['Location']);
	}
	
	public function redirect($location, $status = 301)
	{
		$this->setStatus($status);
		$this->addHeader('Location', $location);
		// $this->send();
		//exit();
		return true;
	}

	public function redirectTo($params, $route = null, $status = 301)
	{
		return $this->redirect($this->router->getBasePath() . $this->router->urlTo($params, $route), $status);
	}

	protected function sendStatus()
	{
		switch ($this->status) {
			case 400 :  $statusmsg = "Bad Request";
			break;
			case 401 :  $statusmsg = "Unauthorized";
			break;
			case 403 :  $statusmsg = "Forbidden";
			break;
			case 404 :  $statusmsg = "Not Found";
			break;
			case 405 :  $statusmsg = "Method Not Allowed";
			break;
			case 406 :  $statusmsg = "Not Acceptable";
			break;
			case 410 :  $statusmsg = "Gone";
			break;
			case 412 :  $statusmsg = "Precondition Failed";
			break;
			case 500 :  $statusmsg = "Internal Server Error";
			break;
			case 501 :  $statusmsg = "Not Implemented";
			break;
			case 502 :  $statusmsg = "Bad Gateway";
			break;
			case 503 :  $statusmsg = "Service Unavailable";
			break;
			case 504 :  $statusmsg = "Gateway Timeout";
			break;
			default :  $statusmsg = "";
		}
		header($this->protocol." " . $this->status . ($statusmsg ? " " . $statusmsg : ""));
	}

	public function sendHeaders()
	{
		if (isset($this->contentType)) {
			if (isset($this->encoding)) {
				header('Content-Type:'.$this->contentType."; charset=".$this->encoding);
			} else {
				header('Content-Type:'.$this->contentType);
			}
		}
		foreach ($this->headers as $key => $value) {
			if (!is_null($value)) {
				header($key . ": " . $value);
			}
		}
	}

	protected function sendBody()
	{
		if (strtoupper($this->encoding) == 'UTF-8') {
			echo utf8_encode($this->content);
		} else {
			echo $this->content;
		}
	}

	public function send()
	{
		if(session_id()) {
			session_write_close();
		}
		$this->sendStatus();
		$this->sendHeaders();
		$this->sendBody();
	}
}