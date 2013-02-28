<?php

/**
 *	@module curl
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service curl_execute
 *	@params url, data, plain
 *	@result response
**/
function curl_execute( $in ){

	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, get( $in, 'url', false, '@io.curl.curl_execute' ) );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

	if( isset( $in[ 'data' ] ) ){
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $in[ 'data' ] );
	}

	if( isset( $in[ 'plain' ] ) )
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: text/plain' ) ); 

	$result = curl_exec ( $ch );
	$info = curl_getinfo( $ch );
	curl_close( $ch );

	if ( $result === false || $info[ 'http_code' ] != 200 ){
		return fail( 'Error in cURL', curl_error($ch).' @curl.execute.service' );
	}

	return success( array( 'response' => $result ), 'Valid cURL Execution' );
}


?>
