/**
 *	@module ui
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service ui_cookie
 *	@params
 *	@result 
**/
iE.ui.cookie = function( $in ){
	if( get( $in, 'key', false ) ){
		$.cookie( $in[ 'key' ], null, { path: get( $in, 'path', '/' ) });
		
		$.cookie( $in[ 'key' ], get( $in, 'value', null ), {
			expires : get( $in, 'expires', 1 ),
			path: get( $in, 'path', '/' )
		});
	}
	else {
		return fail( 'Cookie Key Not Found' );
	}

	return success( $in, 'Valid Cookie UI' );
}

