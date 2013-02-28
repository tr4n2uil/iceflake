/**
 *	@module io
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service io_ajax
 *	@params key, query
 *	@result 
**/
iE.io.ajax = function( $in ){
	var $mem = {};
	for( var $i in $in ){
		$mem[ $i ] = $in[ $i ];
	}
	
	/**
	 *	Load data from server using AJAX
	**/
	$.ajax({
		url: get( $in, 'url', false, '@io.ajax'),
		data: get( $in, 'data', '' ),
		dataType : get( $in, 'type', 'json' ),
		type : get( $in, 'request', 'POST' ),
		processData : get( $in, 'process', false ),
		contentType : get( $in, 'mime', 'application/x-www-form-urlencoded' ),
		async : !get( $in, 'sync', false ),
		
		success : function( $data, $status, $request ){
			iE.fn[ get( $mem, 'fn', 'invoke' ) ]( $mem, $data, $request, $status );
		},
		
		error : function( $request, $status, $error ){
			$mem[ 'valid' ] = false;
			iE.fn[ get( $mem, 'fn', 'invoke' ) ]( $mem, $error, $request, $status );
		}
	});
	
	/**
	 *	@return false 
	 *	to stop default browser event
	**/
	if( !get( $in, 'nostop', false ) ){
		return fail( 'Event Propagation Stopped for AJAX' );
	}

	return success( {}, 'Valid AJAX IO' );
}

