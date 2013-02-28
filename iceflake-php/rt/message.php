<?php

/**
 *	@module rt
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

require_once( IEROOT.'ns/resolve.php' );

/**
 *	@service rt_message
 *	@params key, query
 *	@result 
**/
function rt_message( $in ){
	$idef = get( $in, 'idef', false, '@rt.message.rt_message' );
	$in[ 'query' ] = get( $idef, 'service', false, '@rt.message.rt_message' );
	
	// resolve
	$msg = ns_resolve( $in );
	if( !$msg[ 'valid' ] ) return $msg;

	$mapping = get( $msg, 'mapping', false, '@rt.message.rt_message' );
	$location = $mapping[ 0 ];
	$service = $mapping[ 1 ];
	$output = get( $idef, 'output', array() );

	// args
	foreach ( get( $idef, 'args', array() ) as $key ) {
		$idef = set( $idef, $key, get( $in, $key, false ) );
	}

	// set
	foreach ( get( $mapping, 2, array() ) as $key => $value ) {
		$val = get( $in, $key, false );
		if( $val )
			$idef = set( $idef, $value, $val );
	}

	// input
	foreach ( get( $idef, 'input', array() ) as $key => $value ) {
		$idef = set( $idef, $value, get( $in, $key, false ) );
	}

	// load
	if( !is_file( $location ) )
		return fail( 'File Not Found', 'File to require_once: '.$location );
	
	require_once( $location );

	// run
	$in[ 'idef' ] = $idef = call_user_func( $service, $idef );
	if( !$idef[ 'valid' ] ){
		return fail( $idef[ 'msg' ], $idef[ 'details' ]. ' @'.$service );
	}

	// output
	foreach ( $output as $key => $value ) {
		$in = set( $in, $value, get( $idef, $key, false ) );
	}

	return success( $in, 'Valid Message Execution' );
}


?>
