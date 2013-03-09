<?php

/**
 *	@module jn
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service jn_read
 *	@params
 *	@result 
**/
function jn_read( $in ){
	$path = get( $in, 'path', false, '@jn.read' );
	$block = get( $in, 'block', false, '@jn.read' );
	$key = get( $in, 'key', false, '@jn.read' );
	$head = get( $in, 'head', false );

	if( $head == false ){
		$head = @file_get_contents( $path.'/head/'.$block );
		
		if( $head )
			$head = json_decode( $head, true );
		else
			$head = array( '_order' => array() );
	}

	if( !isset( $head[ $key ] ) ){
		return fail( 'Key Not Found', 'Error reading data for key: '.$key.' @jn.read' );
	}

	list( $pos, $len ) = $head[ $key ];
	$data = @file_get_contents( $path.'/data/'.$block, false, null, $pos, $len );

	if( $data === false ){
		return fail( 'Error Reading File', 'Error reading file: '.get( $in, 'file', false, '@jn.read' ) );
	}

	return success( array( 'data' => $data ), 'Valid Read JN' );
}

?>
