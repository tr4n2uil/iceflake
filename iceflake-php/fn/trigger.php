<?php

/**
 *	@module trigger
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

require_once( IEROOT. 'rt/execute.php' );

function fn_trigger( $in, $key = false ){
	global $iconfig;
	$data = get( $in, 'data', false, 'data not found @fn_trigger' );
	$data = explode( '://', $data );

	switch( $data[ 0 ] ){
		case 'rt':
			$data = $data[ 1 ];
			break;

		case 'mp':
			$data = get( $iconfig, $data[ 1 ], false, 'mapping not found @fn_trigger' );
			break;

		case 'js':
			$data = json_decode( $data[ 1 ], true );
			break;
			
		default:
			$data = $data[ 0 ];
			break;
	}

	$msg = array( 'idef' => $data );

	$msg = rt_execute( $msg );
	if( !$msg[ 'valid' ] )
		return $msg[ 'msg' ];

	if( !$msg[ 'result' ][ 'valid' ] )
		return json_encode( $msg[ 'result' ] )."\n";
	elseif( $key )
		return get( $msg[ 'result' ][ 'idef' ], $key, get( $msg[ 'result' ], $key, 'Key Not Found: '.$key ) );
	else
		return json_encode( $msg[ 'result' ][ 'idef' ] )."\n";
}

?>
