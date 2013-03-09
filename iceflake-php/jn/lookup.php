<?php

/**
 *	@module jn
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service jn_lookup
 *	@params
 *	@result 
**/
function jn_lookup( $in ){
	$path = get( $in, 'path', false, '@jn.lookup' );
	$key = get( $in, 'key', false, '@jn.lookup' );
	$fn = get( $in, 'fn', 'strcmp' );
	
	$block = false;
	$node = $parent = $path.'/tree';
	while( $files = array_slice( scandir( $node ), 2 ) ){
		usort( $files, $fn );

		$pos = array_search_lower( $key, $files, $fn );
		if( $pos === false ){
			$pos = count( $files ) - 1;
		}

		$parent = $node;
		if( $pos == -1 )
			break;

		$node .= '/'.$files[ $pos ];
		if( !is_dir( $node ) ){
			$block = $files[ $pos ];
			break;
		}
	}

	if( get( $in, 'insert', false ) ) {
		require_once( IEROOT. 'jn/tree.php' );
		$msg = jn_treeins( array( 
			'path' => $path,
			'key' => $key,
			'parent' => $parent
		) );

		if( !$msg[ 'valid' ] ) 
			return $msg;

		$block = $key;
	}

	if( $block === false ){
		return fail( 'Error Lookup Key', 'Error looking up key : '. $key .' @jn.lookup' );
	}

	return success( array( 'block' => $block, 'parent' => $parent ), 'Valid Lookup JN' );
}

?>
