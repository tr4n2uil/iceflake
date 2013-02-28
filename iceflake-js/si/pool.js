/**
 *	@module si
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service si_pool
 *	@params 
 *	@result 
**/
iE.si.pool = function( $in ){
	var $key = 'pool://url/' + get( $in, 'url', 'STD_URL' ) + '/data/' + get( $in, 'data', '' );
	var $data = get( iE.state, $key, false );

	if( !get( $in, 'force', false ) && $data ){
		iE.fn[ get( $in, 'fn', 'invoke' ) ]( $in, $data, {}, 'success' );
	}
	else {
		$in[ 'cache-key' ] = $key;
		iE.io[ get( $in, 'io', 'ajax' ) ]( $in );
	}

	if( !get( $in, 'nostop', false ) ){
		return fail( 'Event Propagation Stopped for Pool' );
	}

	return success( {}, 'Valid Pool SI' );
}

