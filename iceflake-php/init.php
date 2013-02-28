<?php

/**
 *	@code init
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

define('IEROOT', dirname(__FILE__).'/' );

$iconfig = array();
$istate = array();

function fail( $msg, $details = '', $die = false ){
	$result = array(
		'valid' => false,
		'msg' => $msg,
		'details' => $details
	);

	if( $die )
		die( json_encode( $result )." $die" );
	return $result;
}

function success( $result = array(), $msg = 'Successfully Executed', $details = 'Successfully Executed' ){
	if( !isset( $result[ 'valid' ] ) )
		$result[ 'valid' ] = true;
	$result[ 'msg' ] = $msg;
	$result[ 'details' ] = $details;
	return $result;
}

function get( $map, $key, $def = false, $die = false ){
	if( $die && !isset( $map[ $key ] ) )
		die( 'Unable to find key in map: '.$key." $die\n" );
	return isset( $map[ $key ] ) ? $map[ $key ] : $def;
} 

function set( $map, $key, $value, $die = false ){
	if( isset( $map[ $key ] ) )
		if( $die )
			die( 'Unable to set key in map: '.$key." $die\n" );
		else
			return $map;

	$map[ $key ] = $value;
	return $map;
}

?>
