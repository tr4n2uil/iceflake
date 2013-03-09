<?php

/**
 *	@module jn
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

require_once( IEROOT.'jn/lookup.php' );
require_once( IEROOT.'jn/read.php' );
require_once( IEROOT.'jn/split.php' );
require_once( IEROOT.'jn/append.php' );

/**
 *	@service jn_create
 *	@params
 *	@result 
**/
function jn_create( $in ){
	$path = get( $in, 'path', false, '@jn.append' );
	
	if( !mkdir( $path.'/data', 0777, true ) )
		return fail( 'Error Making Directory', 'Error mkdir : '. $path .'/data' );

	if( !mkdir( $path.'/head', 0777, true ) )
		return fail( 'Error Making Directory', 'Error mkdir : '. $path .'/head' );
	
	if( !mkdir( $path.'/tree', 0777, true ) )
		return fail( 'Error Making Directory', 'Error mkdir : '. $path .'/tree' );

	return success( array(), 'Valid Create JN' );
}

/**
 *	@service jn_get
 *	@params
 *	@result 
**/
function jn_get( $in ){
	$msg = jn_lookup( $in );
	if( !$msg[ 'valid' ] ) return $msg;

	$in[ 'block' ] = $msg[ 'block' ];
	$in[ 'parent' ] = $msg[ 'parent' ];

	return jn_read( $in );
}

/**
 *	@service jn_set
 *	@params
 *	@result 
**/
function jn_set( $in ){
	$msg = jn_lookup( $in );
	if( !$msg[ 'valid' ] ) return $msg;

	$in[ 'block' ] = $msg[ 'block' ];
	$in[ 'parent' ] = $msg[ 'parent' ];

	$msg = jn_split( $in );
	if( !$msg[ 'valid' ] ) return $msg;

	return jn_append( $in );
}

?>
