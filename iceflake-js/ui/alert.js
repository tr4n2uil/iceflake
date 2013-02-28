/**
 *	@module ui
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service ui_alert
 *	@params key, query
 *	@result 
**/
iE.ui.alert = function( $in ){
	alert( get( $in, 'data', 'Krishna' ) );

	return success( {}, 'Valid Workflow Execution' );
}

