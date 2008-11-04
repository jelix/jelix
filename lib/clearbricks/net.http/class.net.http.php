<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of Clearbricks.
# Copyright (c) 2006 Olivier Meunier and contributors. All rights
# reserved.
#
# Clearbricks is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# Clearbricks is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with Clearbricks; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****
#
# Fully based on Simon Willison's HTTP Client
#
# Version 0.9, 6th April 2003 - Simon Willison ( http://simon.incutio.com/ )
# Manual: http://scripts.incutio.com/httpclient/
#
# Changes:
# - Charset support in POST requests
# - Proxy support through HTTP_PROXY_HOST and HTTP_PROXY_PORT or setProxy()
# - SSL support
# - Handles redirects on other hosts
# - Configurable output

/**
@ingroup CB_NET
@brief Client class for HTTP protocol.

Features:

- Implements a useful subset of the HTTP 1.0 and 1.1 protocols.
- Includes cookie support.
- Ability to set the user agent and referal fields.
- Can automatically handle redirected pages.
- Can be used for multiple requests, with any cookies sent by the server resent
  for each additional request.
- Support for gzip encoded content, which can dramatically reduce the amount of
  bandwidth used in a transaction.
- Object oriented, with static methods providing a useful shortcut for simple
  requests.
- The ability to only read the page headers - useful for implementing tools such
  as link checkers.
- Support for file uploads.

*/
class netHttp extends netSocket
{
	protected $host;					///<	<b>string</b>		Server host
	protected $port;					///<	<b>integer</b>		Server port
	protected $path;					///<	<b>string</b>		Query path
	protected $method;					///<	<b>string</b>		HTTP method
	protected $postdata = '';			///<	<b>string</b>		POST query string
	protected $post_charset;				///<	<b>string</b>		POST charset
	protected $cookies = array();			///<	<b>array</b>		Cookies sent
	protected $referer;					///<	<b>string</b>		HTTP referer
	protected $accept = 'text/xml,application/xml,application/xhtml+xml,text/html,text/plain,image/png,image/jpeg,image/gif,*/*';	///< <b>string</b>	HTTP accept header
	protected $accept_encoding = 'gzip';	///<	<b>string</b>		HTTP accept encoding
	protected $accept_language = 'en-us';	///<	<b>string</b>		HTTP accept language
	protected $user_agent = 'Clearbricks HTTP Client';	///< <b>string</b>	HTTP User Agent
	protected $more_headers = array();		///< <b>array</b>		More headers to be sent
	protected $timeout = 10;				///<	<b>integer</b>		Connection timeout
	protected $use_ssl = false;			///<	<b>boolean</b>		Use SSL connection
	protected $use_gzip = false;			///<	<b>boolean</b>		Use gzip transfert
	protected $persist_cookies = true;	///<	<b>boolean</b>		Allow persistant cookies
	protected $persist_referers = true;	///<	<b>boolean</b>		Allow persistant referers
	protected $debug = false;			///<	<b>boolean</b>		Use debug mode
	protected $handle_redirects = true;	///<	<b>boolean</b>		Follow redirects
	protected $max_redirects = 5;			///<	<b>integer</b>		Maximum redirects to follow
	protected $headers_only = false;		///<	<b>boolean</b>		Retrieve only headers
	
	protected $username;				///<	<b>string</b>		Authentication user name
	protected $password;				///<	<b>string</b>		Authentication password
	
	protected $proxy_host;				///<	<b>string</b>		Proxy server host
	protected $proxy_port;				///<	<b>integer</b>		Proxy server port
	
	# Response vars
	protected $status;					///<	<b>integer</b>		HTTP Status code
	protected $status_string;			///< <b>string</b>		HTTP Status string
	protected $headers = array();			///<	<b>array</b>		Response headers
	protected $content = '';				///<	<b>string</b>		Response body
	
	# Tracker variables
	protected $redirect_count = 0;		///<	<b>integer</b>		Internal redirects count
	protected $cookie_host = '';			///<	<b>string</b>		Internal cookie host
	
	# Output module (null is this->content)
	protected $output = null;			///<	<b>string</b>		Output stream name
	protected $output_h = null;			///<	<b>resource</b>	Output resource
	
	/**
	Constructor. Takes the web server host, an optional port and timeout.
	
	@param	host		<b>string</b>		Server host
	@param	port		<b>integer</b>		Server port
	@param	timeout	<b>integer</b>		Connection timeout
	*/
	public function __construct($host,$port=80,$timeout=null)
	{
		$this->setHost($host,$port);
		
		if (defined('HTTP_PROXY_HOST') && defined('HTTP_PROXY_PORT')) {
			$this->setProxy(HTTP_PROXY_HOST,HTTP_PROXY_PORT);
		}
		
		if ($timeout) {
			$this->setTimeout($timeout);
		}
		$this->_timeout =& $this->timeout;
	}
	
	/**
	Executes a GET request for the specified path. If <var>$data</var> is
	specified, appends it to a query string as part of the get request.
	<var>$data</var> can be an array of key value pairs, in which case a
	matching query string will be constructed. Returns true on success.
	
	@param	path		<b>string</b>		Request path
	@param	data		<b>array</b>		Request parameters
	@return	<b>boolean</b>
	*/
	public function get($path,$data=false)
	{
		$this->path = $path;
		$this->method = 'GET';
		
		if ($data) {
			$this->path .= '?'.$this->buildQueryString($data);
		}
		
		return $this->doRequest();
	}
	
	/**
	Executes a POST request for the specified path. If <var>$data</var> is
	specified, appends it to a query string as part of the get request.
	<var>$data</var> can be an array of key value pairs, in which case a
	matching query string will be constructed. Returns true on success.
	
	@param	path		<b>string</b>		Request path
	@param	data		<b>array</b>		Request parameters
	@param	charset	<b>string</b>		Request charset
	@return	<b>boolean</b>
	*/
	public function post($path,$data,$charset=null)
	{
		if ($charset) {
			$this->post_charset = $charset;
		}
		$this->path = $path;
		$this->method = 'POST';
		$this->postdata = $this->buildQueryString($data);
		return $this->doRequest();
	}
	
	/**
	Prepares Query String for HTTP request. <var>$data</var> is an associative
	array of arguments.
	
	@param	data		<b>array</b>		Query data
	@return	<b>string</b>
	*/
	protected function buildQueryString($data)
	{
		if (is_array($data))
		{
			$qs = array();
			# Change data in to postable data
			foreach ($data as $key => $val)
			{
				if (is_array($val)) {
					foreach ($val as $val2) {
						$qs[] = urlencode($key).'='.urlencode($val2);
					}
				} else {
					$qs[] = urlencode($key).'='.urlencode($val);
				}
			}
			$qs = implode('&',$qs);
		} else {
			$qs = $data;
		}
		
		return $qs;
	}
	
	/**
	Sends HTTP request and stores status, headers, content object properties.
	
	@return	<b>boolean</b>
	*/
	protected function doRequest()
	{
		if ($this->proxy_host && $this->proxy_port) {
			if ($this->use_ssl) {
				throw new Exception('SSL support is not available through a proxy');
			}
			$this->_host = $this->proxy_host;
			$this->_port = $this->proxy_port;
			$this->_transport = '';
		} else {
			$this->_host = $this->host;
			$this->_port = $this->port;
			$this->_transport = $this->use_ssl ? 'ssl://' : '';
		}
		
		#Reset all the variables that should not persist between requests
		$this->headers = array();
		$in_headers = true;
		$this->outputOpen();
		
		$request = $this->buildRequest();
		$this->debug('Request',implode("\r",$request));
		
		$this->open();
		$this->debug('Connecting to '.$this->_transport.$this->_host.':'.$this->_port);
		foreach($this->write($request) as $index => $line)
		{
			# Deal with first line of returned data
			if ($index == 0)
			{
				$line = rtrim($line,"\r\n");
				if (!preg_match('/HTTP\/(\\d\\.\\d)\\s*(\\d+)\\s*(.*)/', $line, $m)) {
					throw new Exception('Status code line invalid: '.$line);
				}
				$http_version = $m[1]; # not used
				$this->status = $m[2];
				$this->status_string = $m[3]; # not used
				$this->debug($line);
				continue;
			}
			
			# Read headers
			if ($in_headers)
			{
				$line = rtrim($line,"\r\n");
				if ($line == '')
				{
					$in_headers = false;
					$this->debug('Received Headers',$this->headers);
					if ($this->headers_only) {
						break;
					}
					continue;
				}
				
				if (!preg_match('/([^:]+):\\s*(.*)/', $line, $m)) {
					# Skip to the next header
					continue;
				}
				$key = strtolower(trim($m[1]));
				$val = trim($m[2]);
				# Deal with the possibility of multiple headers of same name
				if (isset($this->headers[$key])) {
					if (is_array($this->headers[$key])) {
						$this->headers[$key][] = $val;
					} else {
						$this->headers[$key] = array($this->headers[$key], $val);
					}
				} else {
					$this->headers[$key] = $val;
				}
				continue;
			}
			
			# We're not in the headers, so append the line to the contents
			$this->outputWrite($line);
		}
		$this->close();
		$this->outputClose();
		
		# If data is compressed, uncompress it
		if ($this->getHeader('content-encoding') && $this->use_gzip) {
			$this->debug('Content is gzip encoded, unzipping it');
			# See http://www.php.net/manual/en/function.gzencode.php
			$this->content = gzinflate(substr($this->content, 10));
		}
		
		# If $persist_cookies, deal with any cookies
		if ($this->persist_cookies && $this->getHeader('set-cookie') && $this->host == $this->cookie_host)
		{
			$cookies = $this->headers['set-cookie'];
			if (!is_array($cookies)) {
				$cookies = array($cookies);
			}
			
			foreach ($cookies as $cookie)
			{
				if (preg_match('/([^=]+)=([^;]+);/', $cookie, $m)) {
					$this->cookies[$m[1]] = $m[2];
				}
			}
			
			# Record domain of cookies for security reasons
			$this->cookie_host = $this->host;
		}
		
		# If $persist_referers, set the referer ready for the next request
		if ($this->persist_referers) {
			$this->debug('Persisting referer: '.$this->getRequestURL());
			$this->referer = $this->getRequestURL();
		}
		
		# Finally, if handle_redirects and a redirect is sent, do that
		if ($this->handle_redirects)
		{
			if (++$this->redirect_count >= $this->max_redirects)
			{
				$this->redirect_count = 0;
				throw new Exception('Number of redirects exceeded maximum ('.$this->max_redirects.')');
			}
			
			$location = isset($this->headers['location']) ? $this->headers['location'] : '';
			$uri = isset($this->headers['uri']) ? $this->headers['uri'] : '';
			if ($location || $uri)
			{
				if (self::readUrl($location.$uri,$r_ssl,$r_host,$r_port,$r_path,$r_user,$r_pass))
				{
					# If we try to move on another host, remove cookies, user and pass
					if ($r_host != $this->host || $r_port != $this->port) {
						$this->cookies = array();
						$this->setAuthorization(null,null);
						$this->setHost($r_host,$r_port);
					}
					$this->useSSL($r_ssl);
					$this->debug('Redirect to: '.$location.$uri);
					return $this->get($r_path);
				}
			}
			$this->redirect_count = 0;
		}
		return true;
	}
	
	/**
	Prepares HTTP request and returns an array of HTTP headers.
	
	@return	<b>array</b>
	*/
	protected function buildRequest()
	{
		$headers = array();
		
		if ($this->proxy_host) {
			$path = $this->getRequestURL();
		} else {
			$path = $this->path;
		}
		
		# Using 1.1 leads to all manner of problems, such as "chunked" encoding
		$headers[] = $this->method.' '.$path.' HTTP/1.0';
		
		$headers[] = 'Host: '.$this->host;
		$headers[] = 'User-Agent: '.$this->user_agent;
		$headers[] = 'Accept: '.$this->accept;
		
		if ($this->use_gzip) {
			$headers[] = 'Accept-encoding: '.$this->accept_encoding;
		}
		$headers[] = 'Accept-language: '.$this->accept_language;
		
		if ($this->referer) {
			$headers[] = 'Referer: '.$this->referer;
		}
		
		# Cookies
		if ($this->cookies) {
			$cookie = 'Cookie: ';
			foreach ($this->cookies as $key => $value) {
				$cookie .= $key.'='.$value.';';
			}
			$headers[] = $cookie;
		}
		
		# X-Forwarded-For
		$xforward= array($_SERVER['REMOTE_ADDR']);
		if ($this->proxy_host) {
			$xforward[] = $_SERVER['SERVER_ADDR'];
		}
		$headers[] = 'X-Forwarded-For: '.implode(', ',$xforward);
		
		# Basic authentication
		if ($this->username && $this->password) {
			$headers[] = 'Authorization: Basic '.base64_encode($this->username.':'.$this->password);
		}
		
		$headers = array_merge($headers,$this->more_headers);
		
		# If this is a POST, set the content type and length
		if ($this->postdata) {
			$content_type = 'Content-Type: application/x-www-form-urlencoded';
			if ($this->post_charset) {
				$content_type .= '; charset='.$this->post_charset;
			}
			$headers[] = $content_type;
			$headers[] = 'Content-Length: '.strlen($this->postdata);
			$headers[] = '';
			$headers[] = $this->postdata;
		}
		
		return $headers;
	}
	
	/**
	Initializes output handler if <var>$output</var> property is not null and
	is a valid stream.
	*/
	protected function outputOpen()
	{
		if ($this->output) {
			if (($this->output_h = @fopen($this->output,'wb')) === false) {
				throw new Exception('Unable to open output stream '.$this->output);
			}
		} else {
			$this->content = '';
		}
	}
	
	/**
	Closes output module if exists.
	*/
	protected function outputClose()
	{
		if ($this->output && is_resource($this->output_h)) {
			fclose($this->output_h);
		}
	}
	
	/**
	Writes data to output module.
	*/
	protected function outputWrite($c)
	{
		if ($this->output && is_resource($this->output_h)) {
			fwrite($this->output_h,$c);
		} else {
			$this->content .= $c;
		}
	}
	
	/**
	Returns the status code of the response - 200 means OK, 404 means file not
	found, etc.
	
	@return	<b>string</b>
	*/
	public function getStatus()
	{
		return $this->status;
	}
	
	/**
	Returns the content of the HTTP response. This is usually an HTML document.
	
	@return	<b>string</b>
	*/
	public function getContent()
	{
		return $this->content;
	}
	
	/**
	Returns the HTTP headers returned by the server as an associative array.
	
	@return	<b>array</b>
	*/
	public function getHeaders()
	{
		return $this->headers;
	}
	
	/**
	Returns the specified response header, or false if it does not exist.
	
	@param	header	<b>string</b>		Header name
	@return	<b>string</b>
	*/
	public function getHeader($header)
	{
		$header = strtolower($header);
		if (isset($this->headers[$header])) {
			return $this->headers[$header];
		} else {
			return false;
		}
	}
	
	/**
	Returns an array of cookies set by the server.
	
	@return	<b>array</b>
	*/
	public function getCookies()
	{
		return $this->cookies;
	}
	
	/**
	Returns the full URL that has been requested.
	
	@return	<b>string</b>
	*/
	public function getRequestURL()
	{
		$url = 'http'.($this->use_ssl ? 's' : '').'://'.$this->host;
		if (!$this->use_ssl && $this->port != 80 || $this->use_ssl && $this->port != 443) {
			$url .= ':'.$this->port;
		}
		$url .= $this->path;
		return $url;
	}
	
	/**
	Sets server host and port.
	
	@param	host		<b>string</b>		Server host
	@param	port		<b>integer</b>		Server port
	*/
	public function setHost($host,$port=80)
	{
		$this->host = $host;
		$this->port = abs((integer) $port);
	}
	
	/**
	Sets proxy host and port.
	
	@param	host		<b>string</b>		Proxy host
	@param	port		<b>integer</b>		Proxy port
	*/
	public function setProxy($host,$port='8080')
	{
		$this->proxy_host = $host;
		$this->proxy_port = abs((integer) $port);
	}
	
	/**
	Sets connection timeout.
	
	@param	t		<b>integer</b>		Connection timeout
	*/
	public function setTimeout($t)
	{
		$this->timeout = abs((integer) $t);
	}
	
	/**
	Sets the user agent string to be used in the request. Default is
	"Clearbricks HTTP Client".
	
	@param	string	<b>string</b>		User agent string
	*/
	public function setUserAgent($string)
	{
		$this->user_agent = $string;
	}
	
	/**
	Sets the HTTP authorization username and password to be used in requests.
	Don't forget to unset this in subsequent requests to different servers.
	
	@param	username	<b>string</b>		User name
	@param	password	<b>integer</b>		Password
	*/
	public function setAuthorization($username,$password)
	{
		$this->username = $username;
		$this->password = $password;
	}
	
	/**
	Sets additionnal header to be sent with the request.
	
	@param	header	<b>string</b>		Full header definition
	*/
	public function setMoreHeader($header)
	{
		$this->more_headers[] = $header;
	}
	
	/**
	Empty additionnal headers.
	*/
	public function voidMoreHeaders()
	{
		$this->more_headers = array();
	}
	
	
	/**
	Sets the cookies to be sent in the request. Takes an array of name value
	pairs.
	
	@param	array	<b>array</b>		Cookies array
	*/
	public function setCookies($array)
	{
		$this->cookies = $array;
	}
	
	/**
	Sets SSL connection usage.
	*/
	public function useSSL($boolean)
	{
		if ($boolean) {
			if (!in_array('ssl',stream_get_transports())) {
				throw new Exception('SSL support is not available');
			}
			$this->use_ssl = true;
		} else {
			$this->use_ssl = false;
		}
	}
	
	/**
	Specify if the client should request gzip encoded content from the server
	(saves bandwidth but can increase processor time). Default behaviour is
	FALSE.
	*/
	public function useGzip($boolean)
	{
		$this->use_gzip = (boolean) $boolean;
	}
	
	/**
	Specify if the client should persist cookies between requests. Default
	behaviour is TRUE.
	*/
	public function setPersistCookies($boolean)
	{
		$this->persist_cookies = (boolean) $boolean;
	}
	
	/**
	Specify if the client should use the URL of the previous request as the
	referral of a subsequent request. Default behaviour is TRUE.
	*/
	public function setPersistReferers($boolean)
	{
		$this->persist_referers = (boolean) $boolean;
	}
	
	/**
	Specify if the client should automatically follow redirected requests.
	Default behaviour is TRUE.
	*/
	public function setHandleRedirects($boolean)
	{
		$this->handle_redirects = (boolean) $boolean;
	}
	
	/**
	Set the maximum number of redirects allowed before the client quits
	(mainly to prevent infinite loops) Default is 5.
	*/
	public function setMaxRedirects($num)
	{
		$this->max_redirects = abs((integer) $num);
	}
	
	/**
	If TRUE, the client only retrieves the headers from a page. This could be
	useful for implementing things like link checkers. Defaults to FALSE.
	*/
	public function setHeadersOnly($boolean)
	{
		$this->headers_only = (boolean) $boolean;
	}
	
	/**
	Should the client run in debug mode? Default behaviour is FALSE.
	*/
	public function setDebug($boolean)
	{
		$this->debug = (boolean) $boolean;
	}
	
	/**
	Output module init.
	
	@param	out		<b>string</b>		Output stream
	*/
	public function setOutput($out)
	{
		$this->output = $out;
	}
	
	/**
	Static method designed for running simple GET requests. Returns content or
	false on failure.
	
	@param	url		<b>string</b>		Request URL
	@param	output	<b>string</b>		Optionnal output stream
	@return	<b>string</b>
	*/
	public static function quickGet($url,$output=null)
	{
		if (($client = self::initClient($url,$path)) === false) {
			return false;
		}
		$client->setOutput($output);
		$client->get($path);
		return $client->getStatus() == 200 ? $client->getContent() : false;
	}
	
	/**
	Static method designed for running simple POST requests. Returns content or
	false on failure.
	
	@param	url		<b>string</b>		Request URL
	@param	data		<b>array</b>		Array of parameters
	@param	output	<b>string</b>		Optionnal output stream
	@return	<b>string</b>
	*/
	public static function quickPost($url,$data,$output=null)
	{
		if (($client = self::initClient($url,$path)) === false) {
			return false;
		}
		$client->setOutput($output);
		$client->post($path,$data);
		return $client->getStatus() == 200 ? $client->getContent() : false;
	}
	
	/**
	Returns a new instance of the class. <var>$path</var> is an output variable.
	
	@param		url		<b>string</b>		Request URL
	@param[out]	path		<b>string</b>		Resulting path
	@return	<b>netHttp</b>
	*/
	public static function initClient($url,&$path)
	{
		if (!self::readUrl($url,$ssl,$host,$port,$path,$user,$pass)) {
			return false;
		}
		
		$client = new self($host,$port);
		$client->useSSL($ssl);
		$client->setAuthorization($user,$pass);
		
		return $client;
	}
	
	/**
	Parses an URL and fills <var>$ssl</var>, <var>$host</var>, <var>$port</var>,
	<var>$path</var>, <var>$user</var> and <var>$pass</var> variables. Returns
	true on succes.
	*/
	public static function readURL($url,&$ssl,&$host,&$port,&$path,&$user,&$pass)
	{
		$bits = parse_url($url);
		
		if (empty($bits['host'])) {
			return false;
		}
		
		if (empty($bits['scheme']) || !preg_match('%^http[s]?$%',$bits['scheme'])) {
			return false;
		}
		
		$scheme = isset($bits['scheme']) ? $bits['scheme'] : 'http';
		$host = isset($bits['host']) ? $bits['host'] : null;
		$port = isset($bits['port']) ? $bits['port'] : null;
		$path = isset($bits['path']) ? $bits['path'] : '/';
		$user = isset($bits['user']) ? $bits['user'] : null;
		$pass = isset($bits['pass']) ? $bits['pass'] : null;
		
		$ssl = $scheme == 'https';
		
		if (!$port) {
			$port = $ssl ? 443 : 80;
		}
		
		if (isset($bits['query'])) {
			$path .= '?'.$bits['query'];
		}
		
		return true;
	}
	
	/**
	This method is the method the class calls whenever there is debugging
	information available. $msg is a debugging message and $object is an
	optional object to be displayed (usually an array). Default behaviour is to
	display the message and the object in a red bordered div. If you wish
	debugging information to be handled in a different way you can do so by
	creating a new class that extends HttpClient and over-riding the debug()
	method in that class.
	
	@param	msg		<b>string</b>		Debug message
	@param	object	<b>mixed</b>		Variable to print_r
	*/
	protected function debug($msg,$object=false)
	{
		if ($this->debug) {
			echo "-----------------------------------------------------------\n";
			echo '-- netHttp Debug: '.$msg."\n";
			if ($object) {
				print_r($object);
				echo "\n";
			}
			echo "-----------------------------------------------------------\n\n";
		}
	}
}

/* Compatibility to Incutio HttpClient class
   This will be removed soon!             */
class HttpClient extends netHttp
{
	public function getError()
	{
		return null;
	}
}
?>