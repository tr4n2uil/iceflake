<?php

/**
 *	@module http
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

function http_input( $in ){
	switch( $in[ 'type' ] ){
		case 'get':
			$data = $_GET;
			break;
		case 'post':
			$data = $_POST;
			break;
		case 'json':
			$data = json_decode( file_get_contents("php://input"), true );
			if (json_last_error() !== JSON_ERROR_NONE) {
				http_output( fail( 'Error decoding JSON : '.$json_errors[ json_last_error() ] ) );
			}
			break;
		case 'qrystr':
			if( !$data = get( $_SERVER, 'QUERY_STRING', false ) ){
				http_output( fail( 'Error getting Query String' ) ); 
			}
			$data = urldecode( $data );
			break;
		case 'path':
			if( !$data = get( $_SERVER, 'PATH_INFO', false ) ){
				http_output( fail( 'Error getting Path Info' ) ); 	
			}
			$data = urldecode( $data );
			break;
		default:
			if( $_GET )
				$data = $_GET;
			elseif ( $_POST ) 
				$data = $_POST;
			break;
	}

	return $data;
}

function http_output( $in ){
	switch( $in[ 'type' ] ){
		case 'json':
			$data = json_encode( $in[ 'data' ] );
			break;
		case 'plain':
		default:
			$data = $in[ 'data' ];
			break;
	}

	echo $data."\n";
	return true;
}

?>