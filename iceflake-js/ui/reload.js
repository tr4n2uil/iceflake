/**
 *	@module ui
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service ui_reload
 *	@params key, query
 *	@result 
**/
iE.ui.reload = function( $in ){
	if( get( $in, 'next', false ) ){
		window.location = $in[ 'next' ];
	}
	else {
		window.location.reload();
	}

	return success( $in, 'Valid Reload Execution' );
}

