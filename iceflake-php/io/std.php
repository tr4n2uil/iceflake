<?php

/**
 *	@module std
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

function std_input(){
	$in = file_get_contents("php://stdin");
	if( $in === false )
		std_output( fail( 'Error reading STDIN' ) );

	$in = json_decode( $in, true );
	if (json_last_error() !== JSON_ERROR_NONE) {
		std_output( fail( 'Error decoding JSON : '.$json_errors[ json_last_error() ] ) );
	}

	return $in;
}

function std_output( $data ){
	echo json_encode( $data )."\n";
	return true;
}

?>