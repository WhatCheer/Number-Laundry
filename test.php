<?php

	require_once 'vendor/idiorm.php';
	require_once 'vendor/paris.php';

	$config = require_once 'config.php';

	require_once 'model/prefix.php';
	require_once 'number.php';

	ORM::configure( $config['database']['dsn'] );
	ORM::configure( 'username', $config['database']['username'] );
	ORM::configure( 'password', $config['database']['password'] );

	header( 'Content-Type: text/plain' );

	$tests = array(

		'United States: (402) 556-4000' => array(
			'error'  => false,
			'clean'  => '+14025564000',
			'twilio' => array(
				'rate'   => '0.020',
				'prefix' => '1',
			),
			'country' => array(
				'name' => 'United States',
				'code' => 'US',
				'flag' => 'http://numberlaundry-icons.s3.amazonaws.com/us.png',
			),
		),

		'Canada: (604) 872-8666' => array(
			'clean'  => '+16048728666',
			'twilio' => array(
				'rate'   => '0.020',
				'prefix' => '1604',
			),
			'country' => array(
				'name' => 'Canada',
				'code' => 'CA',
				'flag' => 'http://numberlaundry-icons.s3.amazonaws.com/ca.png',
			),
		),

		'Denmark: +45 3313 7111' => array(
			'clean'  => '+4533137111',
			'twilio' => array(
				'rate'   => '0.033',
				'prefix' => '453',
			),
			'country' => array(
				'name' => 'Denmark',
				'code' => 'DK',
				'flag' => 'http://numberlaundry-icons.s3.amazonaws.com/dk.png',
			),
		),
	);

	foreach( $tests as $number => $expected ) {
		$processed = ProcessNumber( $number );

		$comparison = compare_array( $expected, $processed );	

		if( false === $comparison ) {
			print "$number => PASS\n";
		}
		else {
			print "$number => FAIL\n";
			print_r( $comparison );
		}
	}

	function compare_array ( $expected, $testing ) {
		$faults = array();
		foreach( $expected as $key => $value ) {
			if( ! isset( $testing[$key] ) ) {
				$faults[$key] = "Does Not Exist";
			}
			else if ( is_array( $value ) ) {
				$c = compare_array( $value, $testing[$key] );
				if( false !== $c ) {
					$faults[$key] = $c;
				}
			}
			else if ( $value != $testing[$key] ) {
				$faults[$key] = "$value != {$testing[$key]}";
			}
		}
		return ( count( $faults ) == 0 ) ? false : $faults;
	}

