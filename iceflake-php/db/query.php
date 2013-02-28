<?php

/**
 *	@module db
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

define( 'RS_SELECT', 0 );
define( 'RS_UPDATE', 1 );
define( 'RS_DELETE', 1 );
define( 'RS_INSERT', 2 );

// @helper
function str_escape( $param, $stripslashes = false ){
	$msg = db_escape( array(
		'param' => $param,
		'stripslashes' => $stripslashes
	) );

	if( !$msg[ 'valid' ] ) die( 'Error Escaping String: '.$param );
	return $msg[ 'result' ];
}

/**
 *	@service db_query
 *	@params key, query, rstype, rsmode, args, esc, num, chk, cnt, not, err
 *	@result 
**/
function db_query( $in ){
	global $iconfig;

	$query = get( $in, 'query', false, '@db.query.db_query' );
	$cntype = get( $iconfig, get( $in, 'key', 'db' ), false, '@db.query.db_query' );
	$cntype = $cntype[ 'type' ];

	require_once( IEROOT."db/$cntype.php" );

	if( isset( $in[ 'num' ] ) ){
		foreach ( $in[ 'num' ] as $key ) {
			if( !is_numeric( $in[ $key ] ) ){
				return fail( "Value Not Numeric", "Key: ".$key." Value: ".$in[ $key ] );
			}
		}
	}

	db_connect( $in );
	
	if( isset( $in[ 'esc' ] ) ){
		foreach ( $in[ 'esc' ] as $key ) {
			$in[ $key ] = str_escape( $in[ $key ] );
		}
	}

	if( isset( $in[ 'args' ] ) ){
		foreach ($in[ 'args' ] as $key ) {
			if( !isset( $in[ $key ] ) ){
				return fail( "Value Not Found", "Key: ".$key );
			}
			$query = str_replace( '${'.$key.'}', $in[ $key ], $query );
		}
	}

	$in[ 'query' ] = $query;//echo $query."\n";
	$msg = db_result( $in );

	db_close( $in );
	
	if( !$msg[ 'valid' ] ) return $msg;

	if( get( $in, 'chk', false ) && ( get( $in, 'not', true ) ^ ( $msg[ 'count' ] == 1 ) ) ){
		return fail( get( $in, 'err', 'Error in Database' ), 'Non Unique Result' );
	}

	return success( $msg, 'Valid Query Execution' );
}



?>
