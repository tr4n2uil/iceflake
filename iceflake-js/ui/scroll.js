/**
 *	@module ui
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service ui_scroll
 *	@params
 *	@result 
**/
iE.ui.scroll = function( $in ){
	var $el = get( $in, 'el', false );

	if( $el ){
		$( $el ).animate( { scrollTop: get( $in, 'top', 0 ) }, get( $in, 'dur', 500 ) );
	}

	return success( $in, 'Valid Template UI' );
}
