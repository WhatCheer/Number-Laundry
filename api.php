<?php

	require_once 'vendor/skunk.php';
	require_once 'vendor/idiorm.php';
	require_once 'vendor/paris.php';

	$config = require_once 'config.php';

	require_once 'model/prefix.php';
	require_once 'number.php';

	ORM::configure( $config['database']['dsn'] );
	ORM::configure( 'username', $config['database']['username'] );
	ORM::configure( 'password', $config['database']['password'] );

	/////////////////////////////////////////////////////////////////////
	// Use create_function for PHP < 5.3
	/////////////////////////////////////////////////////////////////////

	$s = new Skunk();

	$s->hook(
		'send_head', 
		create_function( '&$s', '
			$s->header( "X-Made-With", "Joy" );
		' )
	);

	$s->hook(
		'before',
		create_function ( '&$s', '
			$s->header( "Content-Type", "application/json" ); 
			$s->body = array();
		' )
	);

	$s->hook(
		'after',
		create_function ( '&$s', '
			if( is_array( $s->body ) ) { 
				$s->body = json_encode( $s->body );
			}
		' )
	);

	$s->get(
		'/launder/bulk(/)',
		create_function ( '&$s',  '
			if( ! isset( $_REQUEST["number"] ) || 0 == count( $_REQUEST["number"] ) ) {
				$s->body["error"] = true;
				$s->body["message"] = "No Numbers Given";
				return;
			}

			foreach( $_REQUEST["number"] as $number ) {
				$s->body[] = ProcessNumber( $number );
			}
		' )
	);

	$s->get(
		'/launder/<number>(/)',
		create_function ( '&$s,$number', '
			$s->body = ProcessNumber( $number );
		' )
	);

	$s->run( $_SERVER['PATH_INFO'] );

