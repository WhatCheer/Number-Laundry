<?php
	/*

		Skunk PHP Framework v0.1.2
		https://github.com/jmhobbs/Skunk
		Copyright 2011, John Hobbs
		Licensed under BSD.

		Includes code from Kohana 3.1.2
		http://kohanaframework.org/
		Copyright 2007-2011 Kohana Team
		Licensed under BSD.

	*/

	class Skunk {

		public $body = '';

		protected $headers = array();

		protected $get = array();
		protected $post = array();

		protected $hooks = array();

		public function get ( $route, $fn ) {
			$this->get[$route] = array( 'compiled' => Skunk::compile_route( $route ), 'callback' => $fn );
		}

		public function post ( $route, $fn ) {
			$this->post[$route] = array( 'compiled' => Skunk::compile_route( $route ), 'callback' => $fn );
		}

		public function run ( $uri = null ) {

			if( is_null( $uri ) ) {
				if( ! isset( $_REQUEST['uri'] ) ) {
					$this->HTTP_404();
				}
				else {
					$uri = $_REQUEST['uri'];
				}
			}

			if( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
				$this->match_route( $this->post, rtrim( $uri, '/' ) );
			}
			else {
				$this->match_route( $this->get, rtrim( $uri, '/' ) );
			}

			$this->send_head();
			$this->send_body();
			exit();
		}

		/**
			Register a hook. Like, um, uh...
		**/
		public function hook ( $hook, $fn ) {
			if( isset( $this->hooks[$hook] ) ) {
				$this->hooks[$hook][] = $fn;
			}
			else {
				$this->hooks[$hook] = array( $fn );
			}
		}

		protected function do_hook ( $hook ) {
			$return = true;
			if( isset( $this->hooks[$hook] ) ) {
				foreach( $this->hooks[$hook] as $hook ) {
					$return = call_user_func( $hook, &$this );
					if( ! $return ) { break; }
				}
			}
			return $return;
		}

		// TODO: More HTTP codes

		public function HTTP_301 ( $location, $error = 'Moved Permanently' ) {
			$this->header( "HTTP/1.0",  "301 $error" );
			$this->header( "Status", "301 $error" );
			$this->header( "Location", $location );
			$this->body = $this->template( $this->_html, array( 'title' => "301 - $error", 'body' => $error ) );
		}

		public function HTTP_404 ( $error = 'Not Found' ) {
			$this->header( "HTTP/1.0",  "404 $error" );
			$this->header( "Status", "404 $error" );
			$this->body = $this->template( $this->_html, array( 'title' => "404 - $error", 'body' => $error ) );
		}

		public function HTTP_500 ( $error = 'Internal Error' ) {
			$this->header( "HTTP/1.0",  "500 $error" );
			$this->header( "Status", "500 $error" );
			$this->body = $this->template( $this->_html, array( 'title' => "500 - $error", 'body' => $error ) );
		}

		/**
			TODO: Even better than this. is_callable, arrays, etc.  Embed mustache?
		**/
		public function template ( $template, $params ) {
			foreach( $params as $param => $value ) {
				$template = str_replace( "{{{$param}}}", $value, $template );
			}

			return $template;
		}

		protected function match_route ( $routes, $uri ) {

			foreach( $routes as $name => $route ) {
				if( false != preg_match( $route['compiled'], $uri, $matches ) ) {

					$this->do_hook( 'before' );

					// We can't use call_user_func_array because it doesn't respect call by ref.
					switch( count( $matches ) ) {
						case 1:
							$route['callback']( $this );
							break;
						case 2:
							$route['callback']( $this, $matches[1] );
							break;
						case 3:
							$route['callback']( $this, $matches[1], $matches[2] );
							break;
						case 4:
							$route['callback']( $this, $matches[1], $matches[2], $matches[3] );
							break;
						case 5:
							$route['callback']( $this, $matches[1], $matches[2], $matches[3], $matches[4] );
							break;
						case 6:
							$route['callback']( $this, $matches[1], $matches[2], $matches[3], $matches[4], $matches[5] );
							break;
						case 7:
							$route['callback']( $this, $matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6] );
							break;
						default:
							// We can only do so much...
							$this->HTTP_500();
					}

					$this->do_hook( 'after' );

					return;
				}
			}

			// If we get to here, nothing ever matched.
			$this->HTTP_404();

		}

		public function header ( $key, $value ) {
			$this->headers[$key] = $value;
		}

		protected function send_head () {
			$this->do_hook( 'send_head' ); 
			foreach( $this->headers as $key => $value ) {
				header( "$key: $value" );
			}
		}

		protected function send_body () {
			$this->do_hook( 'send_body' ); 
			die( $this->body );
		}

		////////////////////////////////////////////////////////////////////////////
		// START ROUTING JACKED FROM KOHANA 3.1.2
		////////////////////////////////////////////////////////////////////////////

		// Defines the pattern of a <segment>
		const REGEX_KEY     = '<([a-zA-Z0-9_]++)>';

		// What can be part of a <segment> value
		const REGEX_SEGMENT = '[^/.,;?\n]++';

		// What must be escaped in the route regex
		const REGEX_ESCAPE  = '[.\\+*?[^\\]${}=!|]';

		public static function compile_route ( $uri, array $regex = NULL ) {

			if ( ! is_string( $uri ) ) return;

			// The URI should be considered literal except for keys and optional parts
			// Escape everything preg_quote would escape except for : ( ) < >
			$expression = preg_replace('#'.Skunk::REGEX_ESCAPE.'#', '\\\\$0', $uri);

			if (strpos($expression, '(') !== FALSE)
			{
				// Make optional parts of the URI non-capturing and optional
				$expression = str_replace(array('(', ')'), array('(?:', ')?'), $expression);
			}

			// Insert default regex for keys
			$expression = str_replace(array('<', '>'), array('(?P<', '>'.Skunk::REGEX_SEGMENT.')'), $expression);

			if ($regex)
			{
				$search = $replace = array();
				foreach ($regex as $key => $value)
				{
					$search[]  = "<$key>".Skunk::REGEX_SEGMENT;
					$replace[] = "<$key>$value";
				}

				// Replace the default regex with the user-specified regex
				$expression = str_replace($search, $replace, $expression);
			}

			return '#^'.$expression.'$#uD';
		}

		////////////////////////////////////////////////////////////////////////////
		// END ROUTING JACKED FROM KOHANA 3.1.2
		////////////////////////////////////////////////////////////////////////////

		protected $_html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head profile="http://purl.org/NET/erdf/profile">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<title>Skunk - {{title}}</title>
		<style type="text/css" media="all">
		</style>
	</head>
	<body>
		<h1>{{title}}</h1>
		{{body}}
	</body>
</html>';

	}

