/**
 *	@module fn
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service fn_trigger
 *	@params key, query
 *	@result 
**/
iE.fn.trigger = function( event ){
	var $data = $( this ).attr( 'data-trigger' );

	try {
		if( $( this ).attr( 'in' ) ){
			var $in = $( this ).attr( 'in' );
		}
		else {
			var $in = { root: $( this ) };
			iE.fn.history( this, 'trigger' );
		}
		
		if( $data ){
			$data = $data.split( '://' );

			switch( $data[ 0 ] ){
				case 'rt':
					$in[ 'idef' ] = $data[ 1 ];
					$in = iE.rt.execute( $in );
					return $in[ 'result' ][ 'valid' ];

				case 'mp':
					$in[ 'idef' ] = get( iE.config.mappings, $data[ 1 ], false, '@fn.trigger' );
					$in = iE.rt.execute( $in );
					return $in[ 'result' ][ 'valid' ];

				case 'js':
					$in[ 'idef' ] = JSON.parse( $data[ 1 ] );
					$in = iE.rt.execute( $in );
					return $in[ 'result' ][ 'valid' ];
					
				case 'fn':
				default:
					$data = $data[ 1 ].split( ' ' );
					for( var $i in $data ){
						var $fx = get( iE.fn, $data[ $i ], false, '@fn.trigger No fn:' + $data[ $i ] );
						if( $fx && !$fx.apply( this ) ){
							return false;
						}
					}
					return true;
			}
		}
	}
	catch( $id ){
		return fail( 'Exception : ' + $id + ' @fn.trigger' );
	}

	return false;
}
