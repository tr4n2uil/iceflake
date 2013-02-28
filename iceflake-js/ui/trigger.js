/**
 *	@module ui
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service ui_trigger
 *	@params
 *	@result 
**/
iE.ui.trigger = function( $in ){
	$el = get( $in, 'el', get( $in, 'root', false ) );

	if( $el ){
		$( $el ).trigger( get( $in, 'event', 'load' ) );
		$in[ 'element' ] = $el;
	}

	return success( $in, 'Valid Trigger UI' );
}

