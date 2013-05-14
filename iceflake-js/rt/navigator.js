/**
 *	@module rt
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@helper parse
 *
**/
iE.rt.parse = function( $data ){
	switch( $data[ 0 ] ){
		case '[':
			$data = $data.substring( 1, $data.length - 1 );
			return $data.split( ',' );

		case '{':
			$data = $data.substring( 1, $data.length - 1 );
			$data = $data.split( ',' );
			var $res = {};
			for( var $i in $data ){
				var $parts = $data[ $i ].split( ':' );
				$res[ $parts[ 0 ] ] = $parts[ 1 ];
			}
			return $res;

		case 't':
			if( $data == "true" )
				return true;

		case 'f':
			if( $data == "false" )
				return false;
			
		default:
			return decodeURIComponent( $data );
	}
}

/**
 *	@service rt_navigator
 *	@params key, query
 *	@result 
**/
iE.rt.navigator = function( $in ){
	var $str = get( $in, 'idef', false, '@rt.navigator' );
	var $_in = $str.split( '/-/' );

	for( var $j in $_in ){
		$str = $_in[ $j ];

		var $idef = {};
		var $args = $str.split( '~' );

		var $path = $args[ 0 ].split( '/' );
		$idef[ 'service' ] = $path[ 0 ];
		var $max = $path.length;
		
		if( !$path[ $max - 1 ] ){
			delete $path[ --$max ];
		}
		
		for( var $i = 1; $i < $max; $i++ ){
			$in[ $i - 1 ] = $path[ $i ];
		}

		if( get( $args, 1, false ) ){
			$path = $args[ 1 ].split( '/' );
			$max = $path.length;
			
			for( var $i = 0; ( $i + 1 ) < $max; $i+=2 ){
				$idef[ $path[ $i ] ] = iE.rt.parse( $path[ $i + 1 ] );
			}
		}

		$_in[ $j ] = $idef;
	}
	

	$in[ 'idef' ] = $_in;
	$in = iE.rt.execute( $in );
	//unset( $in[ 'idef' ] );
	if( !$in[ 'result' ][ 'valid' ] ){
		return $in[ 'result' ];
	}

	$in = $in[ 'result' ];

	return success( $in, 'Valid Navigator Execution' );
}


