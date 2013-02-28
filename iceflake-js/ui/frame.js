/**
 *	@module ui
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service ui_frame
 *	@params key, query
 *	@result 
**/
iE.ui.frame = function( $in ){
	var $name = get( $in, 'name', false );

	for( var i = window.frames.length -1; i >= 0; i-- ){
		var $frame = window.frames[ i ];
		if( $frame.name || false ){
			if( $frame.name == $name ){
				return success( { 'frame': $frame }, 'Valid Frames Execution' );
			}
		}
	}

	return fail( 'Frame Not Found' );
}

