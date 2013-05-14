<?php

/**
 *	@module fs
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

require_once( IEROOT.'fs/journal.php' );

/**
 *	@service fr_new
 *	@params
 *	@result 
**/
function fr_new( $in ){
	$path = get( $in, 'path', false, '@fr.init' );
	$type = get( $in, 'type', false, '@fr.init' );

	// create new journal for type
	$in[ 'name' ] = $type;
	$msg = jn_new( $in );
	if( !$msg[ 'valid' ] )
		return $msg;

	// check schema
	if( !is_dir( $path.'/schema' ) ){
		mkdir( $path.'/schema' );
		file_put_contents( $path.'/schema/archive.conf', '<?php return '. var_export( array( 'name' => 'archive' ), true ) .'; ?>' );
	}

	// add schema conf
	file_put_contents( $path.'/schema/'.$in[ 'type' ].'.conf', '<?php return '. var_export( array( 'entries' => array(
		array( 'type' => $type, 'key' => '{{ name }}', 'keys' => array( 'name' ) )
	) ), true ) .'; ?>' );

	// import archive conf
	$conf = include( $path.'/schema/archive.conf' );
	$archive = $conf[ 'name' ];

	// check archive
	if( !is_dir( $path.'/'.$archive ) ){
		$in[ 'name' ] = $archive;
		$in[ 'chunksize' ] = 4;
		$msg = jn_new( $in );
		if( !$msg[ 'valid' ] )
			return $msg;
	}
	
	return success( array(), 'Valid New FR' );
}

/**
 *	@service fr_data
 *	@params
 *	@result 
**/
function fr_data( $in ){
	$path = get( $in, 'path', false, '@fr.init' );
	$type = get( $in, 'type', false );
	$key = get( $in, 'key', false );
	$data = get( $in, 'data', false );
	$action = get( $in, 'action', $data ? 'add' : 'get' );

	$conf = include( $path.'/schema/archive.conf' );

	// lookup key to archive
	$lookup = false;
	if( $key and $action != 'lookup' ){
		$in[ 'name' ] = $type;
		$in[ 'action' ] = 'get';
		$in[ 'data' ] = false;
		$msg = jn_data( $in );
		if( $msg[ 'valid' ] )
			$lookup = $msg[ 'data' ];
	}
	elseif( $action == 'lookup' ){
		$lookup = $key;
	}

	// write data
	if( $data ){
		$typeconf = include( $path.'/schema/'.$type.'.conf' );

		// data to archive
		$in[ 'key' ] = $lookup;
		$in[ 'name' ] = $conf[ 'name' ];
		$in[ 'action' ] = $action;
		$in[ 'data' ] = json_encode( $data );
		$msg = jn_data( $in );
		if( !$msg[ 'valid' ] )
			return $msg;

		$in[ 'data' ] = $msg[ 'key' ];
		// index by type entries
		if( $key and $action != 'lookup' ){
			foreach( $typeconf[ 'entries' ] as $entry ){
				$k = $entry[ 'key' ];

				foreach( $entry[ 'keys' ] as $val )
					$k = str_replace( "{{ $val }}", $data[ $val ], $k );

				$in[ 'key' ] = $k;
				$in[ 'name' ] = $entry[ 'type' ];
				$msg = jn_data( $in );
				if( !$msg[ 'valid' ] )
					return $msg;
			}
		}
	}
	// read data
	else {
		$in[ 'key' ] = $lookup;
		$in[ 'name' ] = $conf[ 'name' ];
		$in[ 'action' ] = $action;
		$msg = jn_data( $in );
		if( !$msg[ 'valid' ] )
			return $msg;
		$data = $msg[ 'data' ];
	}

	return success( array( 'data' => $data ), 'Valid Data FR' );

}

/**
 *	@service fr_all
 *	@params
 *	@result 
**/
function fr_all( $in ){
	$path = get( $in, 'path', false, '@fr.all' );
	$type = get( $in, 'type', false, '@fr.all' );
	$key = get( $in, 'key', false );
	$action = get( $in, 'action', $key ? 'find' : 'all' );

	$conf = include( $path.'/schema/archive.conf' );
	$result = array();

	// lookup all keys to archive
	$in[ 'name' ] = $type;
	$msg = jn_all( $in );
	if( !$msg[ 'valid' ] )
		return $msg;

	if( $action != 'keys' ){
		$in[ 'name' ] = $conf[ 'name' ];
		foreach( $msg[ 'data' ] as $k => $val ){
			$in[ 'key' ] = $val;
			$in[ 'action' ] = 'get';
			$msg = jn_data( $in );
			if( !$msg[ 'valid' ] )
				return $msg;
			$result[ $k ] = $msg[ 'data' ];
		}
	}
	else
		$result = $msg[ 'keys' ];

	return success( array( 'data' => $result ), 'Valid All FR' );
}

/**
 *	@service fr_wave
 *	@params
 *	@result 
**/
function fr_wave( $in ){
	$path = get( $in, 'path', false, '@fr.wave' );
	$key = get( $in, 'key', false, '@fr.wave' );
	$wave = get( $in, 'wave', false, '@fr.wave' );

	$conf = include( $path.'/schema/archive.conf' );
	$front = array( $key );
	$result = array();

	$pieces = explode( '__', $wave );
	foreach( $pieces as $piece ){
		// get type, bit and key
		list( $t, $b, $k ) = explode( '>', $piece );

		// lookup all keys for bit
		$in[ 'type' ] = $t;
		$in[ 'action' ] = $k == 'keys' ? 'keys' : 'find';
		
		$result = array();
		foreach( $front as $f ){
			$in[ 'key' ] = $b ? $f.'.'.$b : $f;
			$msg = fr_all( $in );
			if( !$msg[ 'valid' ] )
				return $msg;

			switch( $k ){
				case 'keys':
					$l = strlen( $f.'.'.$b ) + 1;
					foreach( $msg[ 'data' ] as $d ){
						$result[] = substr( $d, $l );
					}
					break;

				case 'data':
					$result = array_merge( $result, $msg[ 'data' ] );
					break;

				default:
					foreach( $msg[ 'data' ] as $d ){
						$d = json_decode( $d, true );
						$result[] = $d[ $k ];
					}
					break;
			}

		}

		$front = $result;
	}

	return success( array( 'data' => $result ), 'Valid Wave FR' );
}


?>


