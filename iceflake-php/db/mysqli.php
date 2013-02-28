<?php

/**
 *	@module mysqli
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

// @helper
function get_conn( $in ){
	global $iconfig;
	global $istate;

	$conn = get( $iconfig, get( $in, 'key', 'db' ), false, '@db.mysqli.get_conn' );
	$conn = get( $istate, get( $conn, 'conn', 'conn' ), false, '@db.mysqli.get_conn' );

	return $conn;
}

/**
 *	@service db_conn
 *	@params key
 *	@result 
**/
function db_connect( $in ){
	global $iconfig;
	global $istate;

	$conf = get( $iconfig, get( $in, 'key', 'db' ), false, '@db.mysqli.db_connect' );

	if( get( $conf, 'persist', false ) )
		$conn = mysqli_connect( 'p:'.$conf[ 'host' ], $conf[ 'user' ], $conf[ 'pass' ], $conf[ 'database' ] );
	else
		$conn = mysqli_connect( $conf[ 'host' ], $conf[ 'user' ], $conf[ 'pass' ], $conf[ 'database' ] );
		
	if( mysqli_connect_error() ){
		return fail( 'Error Connecting MySQLi', 'Could not connect to database: '. mysqli_connect_error()."\n", '@db.mysqli.db_connect' );
	}

	$istate = set( $istate, get( $conf, 'conn', 'conn' ), $conn, '@db.mysqli.db_connect' );

	return success( array(), 'Valid MySQLi Connection' );
}

/**
 *	@service db_result
 *	@params key
 *	@result 
**/
function db_result( $in ){
	$conn = get_conn( $in );

	$resultset = $conn->query( get( $in, 'query', false, '@db.mysqli.db_result' ) );
	if( $resultset === false ) 
		return fail( 'Error in Database', 'ERR: '.$conn->error.' @mysqli.result.service' );

	$res = array();
	$cnt = 0;
	switch( get( $in, 'rstype', 0 ) ){
		case 0 : // Select
			while($row = $resultset->fetch_array( get( $in, 'rsmode', MYSQLI_ASSOC ) ))
				$res[] = $row;
			$resultset->close();
			$cnt = count( $res );
			break;
			//return $resultset->fetch_all( $resulttype );
		case 1 : // Update Delete
			$res = $conn->affected_rows;
			break;
		case 2 :  // Insert
			$res = $conn->insert_id;
			break;
	}

	return success( array( 'result' => $res, 'count' => $cnt ), 'Valid MySQLi Result' );
}

/**
 *	@service db_escape
 *	@params key
 *	@result 
**/
function db_escape( $in ){
	$conn = get_conn( $in );

	$param = get( $in, 'param', false, '@db.mysqli.db_escape' );

	if( !get( $in, 'stripslashes', false ) ) {
		if( get_magic_quotes_gpc() ) $param = stripslashes( $param );
	} else {
		if( !get_magic_quotes_gpc() ) $param = addslashes( $param );
	}

	return success( array( 'result' => $conn->real_escape_string( $param ) ), 'Valid MySQLi Escape' );
}

/**
 *	@service db_error
 *	@params key
 *	@result 
**/
function db_error( $in ){
	$conn = get_conn( $in );

	return fail( array( 'error' => $conn->error ), 'Valid MySQLi Error' );
}

/**
 *	@service db_close
 *	@params key
 *	@result 
**/
function db_close( $in ){
	global $iconfig;
	global $istate;

	$conn = get_conn( $in );
	$conn->close();

	$conf = get( $iconfig, get( $in, 'key', 'db' ), false, '@db.mysqli.db_close' );
	$key = get( $conf, 'conn', 'conn' );
	unset( $istate[ $key ] );

	return success( array(), 'Valid MySQLi Exit' );
}


?>
