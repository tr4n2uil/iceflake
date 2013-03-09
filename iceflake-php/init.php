<?php

/**
 *	@code init
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

define( 'IEROOT', dirname(__FILE__).'/' );

$iconfig = array( 'die' => true );
$istate = array();

function fail( $msg, $details = '', $die = false ){
	$result = array(
		'valid' => false,
		'msg' => $msg,
		'details' => $details
	);

	if( $die && $iconfig[ 'die' ] )
		die( json_encode( $result )." $die" );
	return $result;
}

function success( $result = array(), $msg = 'Successfully Executed', $details = 'Successfully Executed' ){
	if( !isset( $result[ 'valid' ] ) )
		$result[ 'valid' ] = true;
	$result[ 'msg' ] = $msg;
	$result[ 'details' ] = $details;
	return $result;
}

function get( $map, $key, $def = false, $die = false ){
	if( $die && !isset( $map[ $key ] ) )
		fail( 'Unable to find key in map: '.$key." $die\n" );
	return isset( $map[ $key ] ) ? $map[ $key ] : $def;
} 

function set( $map, $key, $value, $die = false ){
	if( isset( $map[ $key ] ) )
		if( $die )
			fail( 'Unable to set key in map: '.$key." $die\n" );
		else
			return $map;

	$map[ $key ] = $value;
	return $map;
}

function array_search_lower( $key, $array, $fn = 'strcmp' ){
	foreach( $array as $i => $value ){
			if( call_user_func( $fn, $key, $value ) < 0 )
				return $i - 1;
	}

	return false;
}

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

function next_leaf( $table, $current ){
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


?>
