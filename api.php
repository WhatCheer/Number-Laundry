<?php

	require_once 'vendor/skunk.php';
	require_once 'vendor/idiorm.php';
	require_once 'vendor/paris.php';

	$config = require_once 'config.php';

	require_once 'model/prefix.php';

	ORM::configure( $config['database']['dsn'] );
	ORM::configure( 'username', $config['database']['username'] );
	ORM::configure( 'password', $config['database']['password'] );

	function ProcessNumber ( $number ) {

		$sink = array();

		$clean = $number;
		if( '+' != substr( $number, 0, 1 ) and 10 == strlen( $number ) ) {
			$clean = '1' . $number;
		}
		$clean = '+' . preg_replace( '/[^0-9]/', '', $clean );

		$sink['source'] = $number;
		$sink['error'] = false;

		if( 10 > strlen( $clean ) ) {
			$sink['error'] = true;
			$sink['message'] = 'Not A Valid Number';
			return $sink;
		}

		$sink['clean'] = $clean;
		$sink['twilio'] = null;
		$sink['country'] = null;

		$found = false;

		// Prefix field in DB is 15 chars max, but currently 11 is the longest we have.
		// Keep track of that when you add new prefix data.
		for( $i = 11; $i >= 1; --$i ) {
			$prefix = Model::factory( 'Prefix' )->where( 'prefix', substr( $clean, 1, $i ) )->find_one();
			if( $prefix ) {
				$sink['twilio'] = array( 'rate' => $prefix->twilio_rate, 'prefix' => $prefix->prefix );
				$sink['country'] = array( 'name' => $prefix->country, 'code' => $prefix->country_code );
				$found = true;
			}
			if( $found ) { break; }
		}

		if( $found ) {

			// Fix Canadian numbers (bad Twilio data) :-\
			if( $sink['country']['name'] == 'United States' ) {
				$canadian_area_codes = array(
					403, 587, 780, 587, 604, 778, 250, 204, 506, 709, 867, 902, 905, 
					289, 519, 226, 705, 249, 613, 343, 897, 416, 647, 902, 418, 581,
					450, 579, 514, 438, 819, 306, 867
				);
				if( in_array( substr( $clean, 2, 3 ), $canadian_area_codes ) ) {
					$sink['country']['name'] = 'Canada';
					$sink['country']['code'] = 'CA';
				}
			}

			$sink['country']['flag'] = 'http://numberlaundry-icons.s3.amazonaws.com/' . strtolower( $sink['country']['code'] ) . '.png';
		}

		return $sink;

	}


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

	$s->run();

