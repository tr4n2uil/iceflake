<?php

/**
 *	@module jn
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service jn_treeins
 *	@params
 *	@result
**/
function jn_treeins( $in ){
	$path = get( $in, 'path', false, '@jn.treeins' );
	$node = get( $in, 'parent', false, '@jn.treeins' );
	$key = get( $in, 'key', false, '@jn.treeins' );
	$fn = get( $in, 'fn', 'strcmp' );

	global $iconfig;
	$bf = get( get( $iconfig, 'jn', array() ), 'bfactor', 50 );

	if( touch( $node.'/'.$key ) === false ){
		return fail( 'Error Tree Insert JN', 'Error touching key : '. $key .' @jn.treeins' );
	}

	while( ( $len = count( $files = array_slice( scandir( $node ), 2 ) ) ) > $bf ){
		usort( $files, $fn );
		
		if( $node != $path.'/tree' ){
			$parent = dirname( $node );

			$newdir = $parent.'/'.$files[ $bf ];
			mkdir( $newdir );

			for( $i = $bf; $i < $len; $i++  ){
				rename( $node.'/'.$files[ $i ], $newdir.'/'.$files[ $i ] );
			}

			$node = $parent;
		}
		else {
			mkdir( $node.'/tmp:1' );
			mkdir( $node.'/tmp:2' );

			$tmp1 = $files[ 0 ];
			for( $i = 0; $i < $bf; $i++  ){
				rename( $node.'/'.$files[ $i ], $node.'/tmp:1/'.$files[ $i ] );
			}

			$tmp2 = $files[ $i ];
			for( ; $i < $len; $i++  ){
				rename( $node.'/'.$files[ $i ], $node.'/tmp:2/'.$files[ $i ] );
			}

			rename( $node.'/tmp:1', $node.'/'.$tmp1 );
			rename( $node.'/tmp:2', $node.'/'.$tmp2 );

			break;
		}
	}

	return success( array(), 'Valid Tree Insert JN' );
}

?>
