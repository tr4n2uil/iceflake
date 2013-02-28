/**
 *	@module ui
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service ui_confirm
 *	@params key, query
 *	@result 
**/
iE.ui.confirm = function( $in ){
	if( confirm( get( $in, 'data', 'Are you sure you want to continue ?' ) ) ){
		return success( $in, 'Valid Confirm Execution' );
	}

	return fail( 'User Did Not Confirm' );
}

