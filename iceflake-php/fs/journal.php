<?php

/**
 *	@module fs
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

// 	@helper
function array_search_lower( $key, $array, $fn = 'strcmp' ){
	foreach( $array as $i => $value ){
			if( call_user_func( $fn, $key, $value ) < 0 )
				return $i - 1;
	}

	return false;
}

//	@helper
function tree_next_leaf( $table, $current ){
	$parent = dirname( $current );

	while( $parent != '.' ){
		if( !isset( $table[ $parent ] ) ){
			$files = array_slice( scandir( $parent ), 2 );
			$table[ $parent ] = array_slice( $files, array_search( basename( $current ), $files ) );
		}

		if( count( $table[ $parent ] ) > 1 ){
			array_shift( $table[ $parent ] );
			break;
		}

		$current = $parent;
		$parent = dirname( $parent );

	}

	while( is_dir( $parent ) ){
		if( !isset( $table[ $parent ] ) ){
			$files = array_slice( scandir( $parent ), 2 );
			$table[ $parent ] = $files; // array_slice( $files, array_search( basename( $current ), $files ) );
		}
		$parent .= '/'.$table[ $parent ][ 0 ];
	}

	return $parent;
}

//	@helper
function tree_leaves( $path1, $path2, $count = false ){
	$len1 = strlen( $path1 );
	$len2 = strlen( $path2 );
	$max = $len1 < $len2 ? $len1 : $len2;

	for( $i = 0; $i < $max && ( $path1[ $i ] == $path2[ $i ] ); $i++ );
	$path = substr( $path1, 0, $i );

	if( $path1[ $i ] != '/' )
		$path = dirname( $path );

	$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $path ) );
	$leaves = array();

	$flag = false;
	$i = 0;
	$start = basename( $path1 );
	$end = basename( $path2 );

	while( $it->valid() ) {
		$file = $it->getFilename();

		if( !$flag  ){
			if( $file == $start ){
				$flag = true;
				$leaves[] = $file;
				$i ++;	
			} 
		}
		else {
			$leaves[] = $file;
			$i ++;
			if( $file == $end || $i == $count )
				break;
		}

		$it->next();
	}

	return $leaves;
}

//	@helper 
function tree_balance( $pathfull, $node, $fn = 'strcmp' ){
	global $iconfig;
	$bf = get( get( $iconfig, 'jn', array() ), 'bfactor', 50 );

	while( ( $len = count( $files = array_slice( scandir( $node ), 2 ) ) ) > $bf ){
		usort( $files, $fn );
		
		if( $node != $pathfull ){
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
}

/**
 *	@service jn_init
 *	@params
 *	@result 
**/
function jn_init( $in ){
	$path = get( $in, 'path', false, '@jn.init' );
	$name = get( $in, 'name', false, '@jn.init' );

	$path .= '/'.$name;
	if( is_dir( $path ) )
		return fail( 'Journal Already Exists', 'Error make journal : '. $name );
	
	if( !mkdir( $path.'/data', 0777, true ) )
		return fail( 'Error Making Directory', 'Error mkdir : '. $path .'/data' );

	if( !mkdir( $path.'/head', 0777, true ) )
		return fail( 'Error Making Directory', 'Error mkdir : '. $path .'/head' );
	
	return success( array(), 'Valid Init JN' );
}

/**
 *	@service jn_data
 *	@params
 *	@result 
**/
function jn_data( $in ){
	$path = get( $in, 'path', false, '@jn.init' );
	$name = get( $in, 'name', false, '@jn.init' );
	$key = get( $in, 'key', false, '@jn.init' );
	$data = get( $in, 'data', false );
	$fn = get( $in, 'fn', 'strcmp' );

	$path .= '/'.$name;
	$node = $path.'/head';
	$dnode = $path.'/data';

	// lookup block
	while( $files = array_slice( scandir( $node ), 2 ) ){
		usort( $files, $fn );

		$pos = array_search_lower( $key, $files, $fn );
		if( $pos === false ){
			$pos = count( $files ) - 1;
		}

		if( $pos == -1 )
			break;

		$node .= '/'.$files[ $pos ];
		$dnode .= '/'.$files[ $pos ];
		
		if( !is_dir( $node ) )
			break;
	}

	if( $node == $path.'/head' ){
		$node .= '/'.$key;
		$dnode .= '/'.$key;
	}

	// read block header
	$head = @file_get_contents( $node );
	if( $head )
		$head = json_decode( $head, true );
	else
		$head = array( '_order' => array() );
	$order = $head[ '_order' ];

	if( $data ){
		$pos = array_search_lower( $key, $order, $fn );

		// split block
		if( $pos !== false && $order ){
			$next = $order[ $pos + 1 ];
			$len = $head[ $next ][ 0 ];

			// copy right data
			$rdata = @file_get_contents( $dnode, false, null, $len );
			file_put_contents( dirname( $dnode ).'/'.$next, $rdata );

			// partition headers
			$nxhead = array();
			$nxorder = array_slice( $order, $pos + 1 );
			foreach( $nxorder as $k ){
				$nxhead[ $k ] = $head[ $k ];
				unset( $head[ $k ] );
			}

			$head[ '_order' ] = array_slice( $order, 0, $pos + 1 );
			$nxhead[ '_order' ] = $nxorder;
			$nxhead = json_encode( $nxhead );

			// save headers
			file_put_contents( dirname( $node ).'/'.$next, $nxhead );
			file_put_contents( $node, json_encode( $head ) );

			// truncate left data
			$f = @fopen( $dnode, 'r+' );
			ftruncate( $f, $len );
			fclose( $f );
		}

		// append data
		$pos = ( int ) @filesize( $dnode );
		$len = file_put_contents( $dnode, $data, FILE_APPEND );

		if( $len === false ){
			return fail( 'Error Appending to File', 'Error appending data : '. $data .' to file: '.$dnode );
		}

		// modify header
		$head[ $key ] = array( $pos, $len );
		if( array_search( $key, $head[ '_order' ] ) === false )
			$head[ '_order' ][] = $key;

		$head = json_encode( $head );
		file_put_contents( $node, $head );

		// balance
		tree_balance( $path.'/head', dirname( $node ), $fn );
		tree_balance( $path.'/data', dirname( $dnode ), $fn );
	}
	else {
		// read block data
		if( !isset( $head[ $key ] ) ){
			return fail( 'Key Not Found', 'Error reading data for key: '.$key.' @jn.read' );
		}

		list( $pos, $len ) = $head[ $key ];
		$data = @file_get_contents( $dnode, false, null, $pos, $len );
		if( $data === false ){
			return fail( 'Error Reading File', 'Error reading file: '.$dnode );
		}
	}

	return success( array( 'data' => $data ), 'Valid Data JN' );
}


?>