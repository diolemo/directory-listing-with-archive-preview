<?php

// call the __init() method once all libs load
$__able_lib_callbacks[] = 'REQUEST::__init';

class Request
{
	public static $url;
	public static $remote_addr;
	public static $remote_port;   
	protected static $__source;
	
	public static function __init()
	{
		if (static::is_cli()) 
		{
			Content::$__auto_render = false;
			return;
		}
		
		$_SERVER['REQUEST_PATH'] = explode('?', 
			$_SERVER['REQUEST_URI'])[0];
		
		static::$url = new URL();
		static::$url->scheme = $_SERVER['REQUEST_SCHEME'];
		static::$url->host = $_SERVER['HTTP_HOST'];
		static::$url->port = $_SERVER['SERVER_PORT'];
		static::$url->path = $_SERVER['REQUEST_PATH'];
		static::$url->raw_query = $_SERVER['QUERY_STRING'];
		static::$url->build();

		static::$url->base = Request::__base_url();
		static::$url->local = Request::__local_url();
		
		static::$remote_addr = $_SERVER['REMOTE_ADDR'];
		static::$remote_port = $_SERVER['REMOTE_PORT'];
		
		Request::$__source = &$_REQUEST;
		Post::$__source = &$_POST;
		Get::$__source = &$_GET;
	}
	
	// return the path after able root
	private static function __local_url() 
	{
		$base = Context::$conf['base_url'];
		$path = static::$url->path;      
		if (strpos($path, $base) !== 0)
			throw new Exception();
		return substr($path, strlen($base));
	}

	// return the url before able root
	private static function __base_url()
	{
		$parts = array();
		$parts[] = static::$url->conn;
		$parts[] = Context::$conf['base_url'];
		return implode($parts);
	}

	// is this a cli request?
	public static function is_cli()
	{
		return php_sapi_name() === 'cli';
	}
	
	// return request data (or set it)
	public static function & data($name = null, $value = null)
	{
		if ($name === null) return static::$__source;
		if ($value === null) return static::$__source[$name];
		static::$__source[$name] = $value;
		return static::$__source[$name];
	}
	
	// determine if request data is set ~ true
	public static function evaluate($name, $if_true = true)
	{
		if (!isset(static::$__source[$name])) return false;
		return static::$__source[$name] ? $if_true : false;
	}
	
	// determine if request data is set for <name>
	public static function has($name = null)
	{
		if ($name === null) return !empty(static::$__source);
		return isset(static::$__source[$name]);
	}
	
	// return the part for $index 
	public static function param($index = null)
	{
		$url = new URL();
		$url->path = static::$url->local;
		return $url->param($index);
	}
	
	// return the part for $index 
	public static function section($index = null)
	{
		return static::param($index);
	}
	
	// sends redirect to url but does not exit
	// * $use_base indicates to prefix with base url
	public static function redirect($url = null, $use_base = false)
	{
		if ($url === null)
		{
			$url = Request::$url->url;
			$use_base = false;
		}
		
		if ($use_base === true)
			$url = Context::$conf['base_url'] . $url;
		header(sprintf('Location: %s', $url));
		return $url;
	}
}

class Post extends Request 
{
	protected static $__source;
}

class Get extends Request 
{
	protected static $__source;
}

