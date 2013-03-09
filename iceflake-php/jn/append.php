<?php

/**
 *	@module jn
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service jn_append
 *	@params
 *	@result 
**/
function jn_append( $in ){
	$path = get( $in, 'path', false, '@jn.append' );
	$block = get( $in, 'block', false, '@jn.append' );
	$key = get( $in, 'key', false, '@jn.append' );
	$head = get( $in, 'head', false );

	if( $head == false ){
		$head = @file_get_contents( $path.'/head/'.$block );
		
		if( $head === false )
			$head = array( '_order' => array() );
		else
			$head = json_decode( $head, true );
	}

	$pos = ( int ) @filesize( $path.'/data/'.$block );
	$len = file_put_contents( $path.'/data/'.$block, get( $in, 'data', '' ), FILE_APPEND );

	if( $len === false ){
		return fail( 'Error Appending to File', 'Error appending data : '. get( $in, 'data', '' ) .' to file: '.$path.'/data/'.$block );
	}

	$head[ $key ] = array( $pos, $len );
	if( array_search( $key, $head[ '_order' ] ) === false )
		$head[ '_order' ][] = $key;
	$head = json_encode( $head );

	file_put_contents( $path.'/head/'.$block, $head );

	return success( array(), 'Valid Append JN' );
}

?>
