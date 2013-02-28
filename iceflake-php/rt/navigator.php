<?php

/**
 *	@module rt
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

require_once( IEROOT.'rt/message.php' );

/**
 *	@service rt_navigator
 *	@params key, query
 *	@result 
**/
function rt_navigator( $in ){
	$str = get( $in, 'idef', false, '@rt.navigator.rt_navigator' );
	$_in = explode(  '/-/', $str );

	foreach ( $_in as $j => $str ) {
		$idef = array();
		
		$args = explode( '~', $str );

		$path = explode( '/', $args[ 0 ] );
		$idef[ 'service' ] = $path[ 0 ];
		$max = count( $path );
		
		if( $path[ $max - 1 ] == '' )
			unset( $path[ --$max ] );
		
		for( $i = 1; $i < $max; $i++ ){
			$in[ $i - 1 ] = $path[ $i ];
		}

		if( get( $args, 1, false ) ){
			$path = explode( '/', $args[ 1 ] );
			$max = count( $path );
			
			for( $i = 0; ( $i + 1 ) < $max; $i++ ){
				$idef[ $path[ $i ] ] = rt_parse( $path[ $i + 1 ] );
			}
		}

		$_in[ $j ] = $idef;
	}

	$in[ 'idef' ] = $_in;
	
	$in = rt_execute( $in );
	//unset( $in[ 'idef' ] );
	if( !$in[ 'result' ][ 'valid' ] )
		return $in[ 'result' ];

	$in = $in[ 'result' ];

	return success( $in, 'Valid Navigator Execution' );
}

/**
 *	@helper rt_parse
 *
**/
function rt_parse( $data ){
	if( $data ){
		switch( $data[ 0 ] ){
			case '[':
				$data = substr( $data, 1, -1 );
				return explode( ',', $data );

			case '{':
				$data = substr( $data, 1, -1 );
				$data = explode( ',', $data );
				$res = array();
				foreach( $data as $value ){
					if( $value ){
						$parts = explode( ':', $value );
						$res[ $parts[ 0 ] ] = $parts[ 1 ];
					}
				}
				return $res;

			case 't':
				if( $data == 'true' )
					return true;

			case 'f':
				if( $data == 'false' )
					return false;
				
			default:
				return $data;
		}
	}
}


?>
