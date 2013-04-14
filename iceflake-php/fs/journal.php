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

	return count( $array ) - 1;
}

//	@helper
function tree_next_leaf( $root, $current, &$table, $fn = 'strcmp' ){
	//$current = $root.'/'.$current;
	$cin = $current;
	$parent = dirname( $current );

	while( $parent != $root ){
		if( !isset( $table[ $parent ] ) ){
			$files = array_slice( scandir( $parent ), 2 );
			usort( $files, $fn );
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
			usort( $files, $fn );
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
function tree_lookup( $key, $path, &$node, $conf, $fn = 'strcmp', $first = false ){
	$node_in = $node;

	if( $conf[ 'chunksize' ] ){
		$node .= '/'.implode( '/', str_split( $key, $conf[ 'chunksize' ] ) );
	}
	else {
		while( is_dir( $node ) && $files = array_slice( scandir( $node ), 2 ) ){
			usort( $files, $fn );

			$pos = array_search_lower( $key, $files, $fn );
			if( $pos == -1 )
				break;

			$node .= '/'.$files[ $pos ];
		}

		if( $first && $node == $node_in ){
			$t = array();
			$tmp = tree_next_leaf( $path, $node, $t, $fn );

			if( $tmp )
				$tmp = explode( '/', substr( $tmp, strlen( $node ) + 1 ) );
			else
				$tmp = array( $key );

			foreach( $tmp as $k ){
				$node .= '/'.$key;
			}
		}
	}

	$parent = dirname( $node );
	if( !is_dir( $parent ) ){
		mkdir( $parent, 0777, true );
	}

	return $parent;
}

/**
 *	@service jn_new
 *	@params
 *	@result 
**/
function jn_new( $in ){
	$path = get( $in, 'path', false, '@jn.init' );
	$name = get( $in, 'name', false, '@jn.init' );
	$chunksize = ( int ) get( $in, 'chunksize', 0 );

	$path .= '/'.$name;
	if( is_dir( $path ) )
		return fail( 'Journal Already Exists', 'Error make journal : '. $name );
	
	if( !mkdir( $path.'/data', 0777, true ) )
		return fail( 'Error Making Directory', 'Error mkdir : '. $path .'/data' );

	file_put_contents( $path.'/db.conf', '<?php return '. var_export( array( 
		'chunksize' => $chunksize 
	), true ) .'; ?>' );
	
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
	$conf = include( $path. '/db.conf' );

	// lookup key
	$parent  = tree_lookup( $key, $path, $node, $conf, $fn, true );
	$node = $parent. '/'. $key;
	echo "PATH: $node\n";

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
		if( !$conf[ 'chunksize' ] )
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
	$conf = include( $path. '/db.conf' );
	//$offset = strlen( $path.'/data' );
	$t = array();
	$result = array();

	if( $key ){ 
		$node = $path.'/data';

		// lookup block
		$parent = tree_lookup( $key, $path, $node, $conf, $fn );
		if( $conf[ 'chunksize' ] ){
			$node = tree_next_leaf( $path, $parent, $t, $fn );
		}
		elseif( $node == $path.'/data' ){
			$node = tree_next_leaf( $path, $node, $t, $fn );
		}
		
		$ln = strlen( $key );
	}
	else {
		// find first
		$node = tree_next_leaf( $path, $path.'/data', $t, $fn );
	}

	$flag = true;
	while( $flag && $node ){
		$k = basename( $node );
		echo $node;

		// check bounds
		if( $key ){
			$ks = substr( $k, 0, $ln );
			if( $ks < $key ){
				$node = tree_next_leaf( $path, $node, $t, $fn );
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
			$result[ $k ] = $data;
		}

		// proceed to next
		$node = tree_next_leaf( $path, $node, $t, $fn );
	}

	return success( array( 'data' => $result ), 'Valid All JN' );
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
		$id = 0;
		fscanf( $fp, "%f", $id );
		if( !$id )
			$id = 1;

		$sid = sprintf( "%.0f", ( $auto ? $auto : ( $id + 1 ) ) );
		file_put_contents( $path, $sid );

		flock( $fp, LOCK_UN );
	}
	else {
		return fail( 'Error Acquiring Lock', "FILE: $path @jn_data" );
	}

	fclose( $fp );
	return success( array( 'id' => $sid ), 'Valid Id JN' );
}


?>