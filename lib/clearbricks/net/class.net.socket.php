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

/**
@defgroup CB_NET Clearbricks network classes
@ingroup CLEARBRICKS
*/

/**
@ingroup CB_NET
@brief Sockets handler

Base sockets client class connections. Once socket is open, you can read
results with a non recursive iterator in foreach() loop.
*/
class netSocket
{
	protected $_host;			///< <b>string</b>		Server host
	protected $_port;			///< <b>integer</b>		Server port
	protected $_transport = '';	///< <b>string</b>		Server transport
	protected $_timeout;		///< <b>integer</b>		Connection timeout
	
	protected $_handle;			///< <b>resource</b>	Resource handler
	
	/**
	Class constructor
	
	@param	host		<b>string</b>		Server host
	@param	port		<b>integer</b>		Server port
	@param	timeout	<b>integer</b>		Connection timeout
	*/
	public function __construct($host,$port,$timeout=10)
	{
		$this->_host = $host;
		$this->_port = abs((integer) $port);
		$this->_timeout = abs((integer) $timeout);
	}
	
	/**
	Object destructor
	
	Calls close() method
	*/
	public function __destruct()
	{
		$this->close();
	}
	
	/**
	Returns host if <var>$host</var> is not set, sets it otherwise.
	
	@param	host		<b>string</b>		Server host
	*/
	public function host($host=null)
	{
		if ($host) {
			$this->_host = $host;
			return true;
		}
		return $this->_host;
	}
	
	/**
	Returns port if <var>$port</var> is not set, sets it otherwise.
	
	@param	port		<b>integer</b>		Server port
	*/
	public function port($port=null)
	{
		if ($port) {
			$this->_port = abs((integer) $port);
			return true;
		}
		return $this->_port;
	}
	
	/**
	Returns timeout if <var>$timeout</var> is not set, sets it otherwise.
	
	@param	timeout	<b>integer</b>		Connection timeout
	*/
	public function timeout($timeout)
	{
		if ($timeout) {
			$this->_timeout = abs((integer) $timeout);
			return true;
		}
		return $this->_timeout;
	}
	
	/**
	Opens socket connection. Returns an object of type netSocketIterator which
	can be iterate with a simple foreach loop.
	
	@return	<b>netSocketIterator</b>
	*/
	public function open()
	{
		$handle = @fsockopen($this->_transport.$this->_host,$this->_port,$errno,$errstr,$this->_timeout);
		if (!$handle) {
			throw new Exception('Socket error: '.$errstr.' ('.$errno.')');
		}
		$this->_handle = $handle;
		return $this->iterator();
	}
	
	/**
	Closes socket connection
	*/
	public function close()
	{
		if ($this->isOpen()) {
			fclose($this->_handle);
			$this->_handle = null;
		}
	}
	
	/**
	Sends data to current socket and returns an object of type
	netSocketIterator which can be iterate with a simple foreach loop.
	
	<var>$data</var> can be a string or an array of simple lines.
	
	Example:
	
	@verbatim
	<?php
	$s = new netSocket('www.google.com',80,2);
	$s->open();
	$data = array(
		'GET / HTTP/1.0'
	);
	foreach($s->write($data) as $v) {
		echo $v."\n";
	}
	$s->close();
	?>
	@endverbatim
	
	@param	data		<b>mixed</b>		Data to send
	@return	<b>netSocketIterator</b>
	*/
	public function write($data)
	{
		if (!$this->isOpen()) {
			return false;
		}
		
		if (is_array($data)) {
			$data = implode("\r\n",$data)."\r\n\r\n";
		}
		
		fwrite($this->_handle,$data);
		return $this->iterator();
	}
	
	/**
	Flushes socket write buffer.
	*/
	public function flush()
	{
		if (!$this->isOpen()) {
			return false;
		}
		
		fflush($this->_handle);
	}
	
	/**
	Returns an object of type netSocketIterator
	*/
	protected function iterator()
	{
		if (!$this->isOpen()) {
			return false;
		}
		return new netSocketIterator($this->_handle);
	}
	
	/**
	Returns true if socket connection is open.
	
	@return	<b>boolean</b>
	*/
	public function isOpen()
	{
		return is_resource($this->_handle);
	}
}

class netSocketIterator implements Iterator
{
	protected $_handle;
	protected $_index;
	
	public function __construct(&$handle)
	{
		if (!is_resource($handle)) {
			throw new Exception('Handle is not a resource');
		}
		$this->_handle =& $handle;
		$this->_index = 0;
	}
	
	/* Iterator methods
	--------------------------------------------------- */
	public function rewind() {
		# Nothing
	}
	
	public function valid() {
		return !feof($this->_handle);
	}
	
	public function next() {
		$this->_index++;
	}
	
	public function key() {
		return $this->_index;
	}
	
	public function current() {
		return fgets($this->_handle,4096);
	}
}
?>