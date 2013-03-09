<?php

/**
 *	@module jn
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/


/**
 *	@service jn_split
 *	@params
 *	@result
**/
function jn_split( $in ){
	$path = get( $in, 'path', false, '@jn.split' );
	$block = get( $in, 'block', false, '@jn.split' );
	$key = get( $in, 'key', false, '@jn.split' );
	
	$head = @file_get_contents( $path.'/head/'.$block );
	
	if( $head )
		$head = json_decode( $head, true );
	else
		$head = array( '_order' => array() );

	$order = $head[ '_order' ];

	$pos = array_search_lower( $key, $order, get( $in, 'fn', 'strcmp' ) );
	if( $pos !== false ){
		require_once( IEROOT. 'jn/lookup.php' );

		//if( $pos == -1 ){
		//	return fail( 'Unexpected POS -1', $block.'@jn.split' );
		//}

		$next = $order[ $pos + 1 ];
		$len = $head[ $next ][ 0 ];
		$data = @file_get_contents( $path.'/data/'.$block, false, null, $len );

		$msg = jn_lookup( array( 
			'path' => $path,
			'key' => $next,
			'insert' => true
		) );
		
		if( !$msg[ 'valid' ] ) 
			return $msg;

		file_put_contents( $path.'/data/'.$next, $data );

		$nxhead = array();
		$nxorder = array_slice( $order, $pos + 1 );
		foreach( $nxorder as $k ){
			$nxhead[ $k ] = $head[ $k ];
			unset( $head[ $k ] );
		}

		$head[ '_order' ] = array_slice( $order, 0, $pos + 1 );
		$nxhead[ '_order' ] = $nxorder;
		$nxhead = json_encode( $nxhead );

		file_put_contents( $path.'/head/'.$next, $nxhead );

		file_put_contents( $path.'/head/'.$block, json_encode( $head ) );

		$f = @fopen( $path.'/data/'.$block, 'r+' );
		ftruncate( $f, $len );
		fclose( $f );
	}

	return success( array( 'head' => $head, 'block' => $block ), 'Valid Split JN' );
}

?>
