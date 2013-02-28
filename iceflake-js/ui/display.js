/**
 *	@module ui
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service ui_display
 *	@params
 *	@result 
**/
iE.ui.display = function( $in ){
	$el = get( $in, 'el', get( $in, 'root', false ) );
	
	if( $el ){
		$el = $( $el );

		switch( get( $in, 'act', 'filter' ) ){
			case 'show':
				$el.show();
				break;
			case 'hide':
				$el.hide();
				break;
			case 'toggle':
				$el.toggle();
				break;
			case 'filter':
			default:
				$sel = get( $in, 'sel', '.tile-content' );
				$tile = get( $in, 'tile', '#main-tile' )
				$el.children( $sel ).hide();
				$el.children( $tile ).show();
				break;
		}

		$in[ 'element' ] = $el;
	}

	return success( $in, 'Valid Attribute UI' );
}

