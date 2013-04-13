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
function tree_next_leaf( $root, $current, &$table ){
	//$current = $root.'/'.$current;
	$cin = $current;
	$parent = dirname( $current );

	while( $parent != $root ){
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
			if( !$files )
				return false;

			$table[ $parent ] = $files; // array_slice( $files, array_search( basename( $current ), $files ) );
		}
		$parent .= '/'.$table[ $parent ][ 0 ];
	}

	return $parent == $cin ? false : $parent;
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

//	@helper
function tree_lookup( $key, $path, &$node, $fn = 'strcmp' ){
	while( $files = array_slice( scandir( $node ), 2 ) ){
		usort( $files, $fn );

		$pos = array_search_lower( $key, $files, $fn );
		if( $pos === false ){
			$pos = count( $files ) - 1;
		}

		if( $pos == -1 )
			break;

		$node .= '/'.$files[ $pos ];
		
		if( !is_dir( $node ) )
			break;
	}
}

/**
 *	@service jn_new
 *	@params
 *	@result 
**/
function jn_new( $in ){
	$path = get( $in, 'path', false, '@jn.init' );
	$name = get( $in, 'name', false, '@jn.init' );

	$path .= '/'.$name;
	if( is_dir( $path ) )
		return fail( 'Journal Already Exists', 'Error make journal : '. $name );
	
	if( !mkdir( $path.'/data', 0777, true ) )
		return fail( 'Error Making Directory', 'Error mkdir : '. $path .'/data' );

	//if( !mkdir( $path.'/head', 0777, true ) )
	//	return fail( 'Error Making Directory', 'Error mkdir : '. $path .'/head' );
	
	return success( array(), 'Valid New JN' );
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
	$node = $path.'/data';

	// lookup key
	tree_lookup( $key, $path, $node, $fn );

	// insert new at start
	if( $node == $path.'/data' ){
		$t = array();
		$tmp = tree_next_leaf( $path, $path.'/.', $t );

		if( $tmp )
			$tmp = explode( '/', substr( $tmp, strlen( $node ) + 1 ) );
		else
			$tmp = array( $key );

		foreach( $tmp as $k ){
			$node .= '/'.$key;
		}

		$parent = dirname( $node );
		if( !is_dir( $parent ) ){
			mkdir( $parent, 0777, true );
		}
	}

	// ensure file key match
	if( basename( $node ) != $key )
		$node = dirname( $node ).'/'.$key;

	// open file lock
	$fp = fopen( $node, 'c+' );
	if( $data && flock( $fp, LOCK_EX ) ){
		// write data
		$len = file_put_contents( $node, $data );
		flock( $fp, LOCK_UN );

		// exit on error
		if( $len === false ){
			fclose( $fp );
			return fail( 'Error Writing to File', 'Error writing data : '. $data .' to file: '.$node );
		}

		// balance
		tree_balance( $path.'/data', dirname( $node ), $fn );
	}
	elseif( !$data && flock( $fp, LOCK_SH ) ){
		// read data
		$data = @file_get_contents( $node );
		flock( $fp, LOCK_UN );

		// exit on error
		if( $data === false ){
			fclose( $fp );
			return fail( 'Error Reading from File', 'Error reading from file: '.$node );
		}
	}
	else {
		return fail( 'Error Acquiring Lock', "FILE: $node @jn_data" );
	}

	// close file lock
	fclose( $fp );
	return success( array( 'data' => $data ), 'Valid Data JN' );

}

/**
 *	@service jn_all
 *	@params
 *	@result 
**/
function jn_all( $in ){
	$path = get( $in, 'path', false, '@jn.init' );
	$name = get( $in, 'name', false, '@jn.init' );
	$key = get( $in, 'key', false );
	$fn = get( $in, 'fn', 'strcmp' );

	$path .= '/'.$name;
	//$offset = strlen( $path.'/data' );
	$t = array();
	$result = array();
	$keys = array();

	if( $key ){ 
		$node = $path.'/data';

		// lookup block
		tree_lookup( $key, $path, $node, $fn );
		if( $node == $path.'/data' ){
			$node = tree_next_leaf( $path.'/data', $path.'/.', $t );
		}

		$ln = strlen( $key );
	}
	else {
		// find first
		$node = tree_next_leaf( $path.'/data', $path.'/.', $t );
	}

	$flag = true;
	while( $flag && $node ){
		$k = basename( $node );

		// check bounds
		if( $key ){
			$ks = substr( $k, 0, $ln );
			if( $ks < $key ){
				$node = tree_next_leaf( $path, $node, $t );
				continue;
			}
			elseif( $ks != $key ){
				$flag = false;
				break;
			}
		}


		// lock
		$fp = fopen( $node, 'r+' );
		if( flock( $fp, LOCK_SH ) ){
			$data = @file_get_contents( $node );
			flock( $fp, LOCK_UN );
		}
		else {
			return fail( 'Error Acquiring Lock', "FILE: $node @jn_data" );
		}

		fclose( $fp );
		if( $data ){
			$k = basename( $node );
			$keys[] = $k;
			$result[ $k ] = $data;
		}

		// proceed to next
		$node = tree_next_leaf( $path, $node, $t );
	}

	return success( array( 'data' => $result, 'keys' => $keys ), 'Valid All JN' );
}


/**
 *	@service jn_data
 *	@params
 *	@result 
**/
function jn_data_old( $in ){
	$path = get( $in, 'path', false, '@jn.init' );
	$name = get( $in, 'name', false, '@jn.init' );
	$key = get( $in, 'key', false, '@jn.init' );
	$data = get( $in, 'data', false );
	$fn = get( $in, 'fn', 'strcmp' );

	$path .= '/'.$name;
	$node = $path.'/head';
	$dnode = $path.'/data';

	// lookup block
	tree_lookup( $key, $path, $node, $dnode, $fn );

	if( $node == $path.'/head' ){
		$t = array();
		$tmp = tree_next_leaf( $path, $path.'/.', $t );

		if( $tmp )
			$tmp = explode( '/', substr( $tmp, strlen( $dnode ) + 1 ) );
		else
			$tmp = array( $key );

		foreach( $tmp as $k ){
			$node .= '/'.$key;
			$dnode .= '/'.$key;
		}

		$parent = dirname( $node );
		if( !is_dir( $parent ) ){
			mkdir( $parent, 0777, true );
			mkdir( dirname( $dnode ), 0777, true );
		}
	}
	
	// lock
	$fp = fopen( $node, 'c+' );
	if( ( $data && flock( $fp, LOCK_EX ) ) || ( !$data && flock( $fp, LOCK_SH ) ) ){
		// read block header
		$head = @file_get_contents( $node );
		if( $head )
			$head = json_decode( $head, true );
		else
			$head = array( '_order' => array() );

		if( $head )
			$order = $head[ '_order' ];
		else {
			flock( $fp, LOCK_UN );
			fclose( $fp );
			return fail( 'Error Reading Header', "FILE: $node @jn.data" );
		}

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
					$nxhead[ $k ] = array( $head[ $k ][ 0 ] - $len, $head[ $k ][ 1 ] );
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

			// split onmax
			global $iconfig;
			$mx = get( get( $iconfig, 'jn', array() ), 'maxfsize', 4294967296 );
			$size = ( int ) @filesize( $dnode );

			if( ( $size + strlen( $data ) ) > $mx ){
				$size = 0;
				$head = array( '_order' => array() );
				$node = dirname( $node ).'/'.$key;
				$dnode = dirname( $dnode ).'/'.$key;
			}

			// append data
			$pos = $size;
			$len = file_put_contents( $dnode, $data, FILE_APPEND );

			if( $len === false ){
				flock( $fp, LOCK_UN );
				fclose( $fp );
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
				flock( $fp, LOCK_UN );
				fclose( $fp );
				return fail( 'Key Not Found', 'Error reading data for key: '.$key.' @jn.read' );
			}

			list( $pos, $len ) = $head[ $key ];
			$data = @file_get_contents( $dnode, false, null, $pos, $len );
			if( $data === false ){
				flock( $fp, LOCK_UN );
				fclose( $fp );
				return fail( 'Error Reading File', 'Error reading file: '.$dnode );
			}
		}
		flock( $fp, LOCK_UN );
	}
	else {
		return fail( 'Error Acquiring Lock', "FILE: $node @jn_data" );
	}

	fclose( $fp );
	return success( array( 'data' => $data ), 'Valid Data JN' );
}

/**
 *	@service jn_all
 *	@params
 *	@result 
**/
function jn_all_old( $in ){
	$path = get( $in, 'path', false, '@jn.init' );
	$name = get( $in, 'name', false, '@jn.init' );
	$key = get( $in, 'key', false );
	$fn = get( $in, 'fn', 'strcmp' );

	$path .= '/'.$name;
	//$offset = strlen( $path.'/data' );
	$t = array();
	$result = array();
	$keys = array();

	if( $key ){ 
		$node = $path.'/head';
		$dnode = $path.'/data';

		// lookup block
		tree_lookup( $key, $path, $node, $dnode, $fn );
		if( $node == $path.'/head' ){
			$dnode = tree_next_leaf( $path, $path.'/.', $t );
			$node = tree_next_leaf( $path.'/head', $path.'/.', $t );
		}

		$ln = strlen( $key );
	}
	else {
		$dnode = tree_next_leaf( $path, $path.'/.', $t );
		$node = tree_next_leaf( $path.'/head', $path.'/.', $t );
		//$node = $path.'/head'.substr( $node, $offset );
	}

	$flag = true;
	while( $flag && $node && $dnode ){
		//echo "NODE: $node\nDNODE: $dnode\n";
		// lock
		$fp = fopen( $node, 'r+' );
		if( flock( $fp, LOCK_SH ) ){
			$head = json_decode( @file_get_contents( $node ), true );
			
			if( !$head ) break;
			$data = @file_get_contents( $dnode );

			flock( $fp, LOCK_UN );
		}
		else {
			return fail( 'Error Acquiring Lock', "FILE: $node @jn_data" );
		}

		fclose( $fp );
		
		foreach( $head[ '_order' ] as $k ){
			if( $key ){
				$ks = substr( $k, 0, $ln );
				if( $ks < $key )
					continue;
				elseif( $ks != $key ){
					$flag = false;
					break;
				}
			}

			list( $pos, $len ) = $head[ $k ];
			$keys[] = $k;
			$result[ $k ] = substr( $data, $pos, $len );
		}

		$node = tree_next_leaf( $path, $node, $t );
		$dnode = tree_next_leaf( $path, $dnode, $t );
	}

	return success( array( 'data' => $result, 'keys' => $keys ), 'Valid All JN' );
}


/**
 *	@service jn_id
 *	@params
 *	@result 
**/
function jn_id( $in ){
	$path = get( $in, 'path', false, '@jn.init' );
	$name = get( $in, 'name', false, '@jn.init' );
	$auto = get( $in, 'auto', false );

	$path .= '/'.$name.'/id';

	$fp = fopen( $path, 'c+' );
	if( flock( $fp, LOCK_EX ) ){
		$id = fread( $fp, 1024 );
		if( !$id )
			$id = "1";

		$id = ( int ) $id;
		rewind( $fp );
		ftruncate( $fp, 0 );

		fwrite( $fp, ( string ) ( $auto ? $auto : ( $id + 1 ) ) );
		flock( $fp, LOCK_UN );
	}
	else {
		return fail( 'Error Acquiring Lock', "FILE: $path @jn_data" );
	}

	fclose( $fp );
	return success( array( 'id' => $id ), 'Valid Id JN' );
}



?>