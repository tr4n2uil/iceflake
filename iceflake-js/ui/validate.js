/**
 *	@module ui
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service ui_validate
 *	@params key, query
 *	@result 
**/
iE.ui.validate = function( $in ){
	var $res = true;

	var validate = function( $index, $el ){
		var $data = $( this ).attr( 'data-validate' );
		if( $data ){
			$data = $data.split( ' ' );

			for( var $i in $data ) {
				var $fx = $res = get( iE.fn, $data[ $i ], false, '@ui.validate No fn:' + $data[ $i ] );
				if( $fx ){
					$res = $fx( $( this ) );
				}
				if( !$res ){
					return false;
				}
			};
		}
	}

	$( get( $in, 'sel', false, ' @ui.validate' ) ).each( validate );

	if( $res ){
		return success( $in, 'Valid Confirm Execution' );
	}

	return fail( 'User Did Not Confirm' );
}
