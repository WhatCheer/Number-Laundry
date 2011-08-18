<?php

	function ProcessNumber ( $number ) {

		$sink = array();

		$clean = preg_replace( '/[^0-9]/', '', $number );
		if( '+' != substr( preg_replace( '/[^0-9\+]/', '', $number ), 0, 1 ) and 10 == strlen( $clean ) ) {
			$clean = '1' . $clean;
		}
		$clean = '+' . $clean;

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
