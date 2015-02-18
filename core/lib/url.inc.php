<?php

class URL
{
	const SCHEME_HTTP = 'http';
	const SCHEME_HTTPS = 'https';

	protected $__parsed = false;
	
	// local url relative system
	// * Request::$url->local
	public $local = null;

	// base url relative system
	// * Request::$url->base
	public $base = null;

	// scheme://[user[:pass]]@host
	public $conn = null;
	
	public $fragment = null;
	public $host = null;
	public $pass = null;
	public $path = null;
	public $port = null;
	public $query = null;
	public $raw_query = null;
	public $scheme = null;
	public $user = null;
	
	// entire url
	public $url = null;
	
	public static function __parse(&$url)
	{
		$instance = new static($url, true);
		return $instance;
	}
	
	public function __construct(&$url = null, $parse = false)
	{
		$this->url = $url;
		if (!$parse) return;
		$this->parse();
	}
	
	public function build()
	{
		if (!$this->scheme)
			$this->scheme = static::SCHEME_HTTP;

		$bits = (array) $this;
		$bits['query'] = $bits['raw_query'];
		$this->url = http_build_url($bits);  
			 
		$conn = array();
		$conn['host'] = &$bits['host'];
		$conn['pass'] = &$bits['pass'];
		$conn['port'] = &$bits['port'];
		$conn['scheme'] = &$bits['scheme'];
		$conn['user']  = &$bits['user'];
		$conn['path'] = '/';
		
		$this->conn = http_build_url($conn);
		$this->conn = substr($this->conn, 0, -1);
		
		return $this->url;
	}
	
	public function parse($url = null)
	{
		if ($url === null && $this->__parsed) return $this;
		if ($url !== null) $this->url = $url;
		$bits = parse_url($this->url);
				
		$this->fragment   = $bits['fragment'];
		$this->host       = $bits['host'];
		$this->pass       = $bits['pass'];
		$this->path       = $bits['path'];
		$this->port       = $bits['port'];
		$this->raw_query  = $bits['query'];
		$this->scheme     = $bits['scheme'];
		$this->user       = $bits['user'];
		
		if ($this->raw_query !== null)
			parse_str($this->raw_query, $this->query);    
		
		$this->__parsed = true;           
		return $this;
	}
	
	// url can be divided into parts
	// at each slash and this will return
	// the part at the given index
	public function param($index = null)
	{
		$params = array_filter(explode('/', $this->path));
		if (strlen($params[0]) === 0)
			array_shift($params);
		if ($index === null)
			return array_map('rawurldecode', $params);

		if ($index < 0) 
		{
			$slice = array_slice($params, $index, 1);
			if (!isset($slice[0])) return null;
			return rawurldecode($slice[0]);
		}

		if (!isset($params[$index])) return null;
		return rawurldecode($params[$index]);
	}
	
}

