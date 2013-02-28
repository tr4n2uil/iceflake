/**
 *	@module ui
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service ui_attribute
 *	@params
 *	@result 
**/
iE.ui.attribute = function( $in ){
	var $el = get( $in, 'el', get( $in, 'root', false ) );
	var $attr = get( $in, 'attr', 'disabled' );
	var $value = get( $in, 'val', false );

	if( $el ){
		if( $value ){
			$( $el ).attr( $attr, $value );
		}
		else {
			$( $el ).removeAttr( $attr );
		}

		$in[ 'element' ] = $el;
	} 

	return success( $in, 'Valid Attribute UI' );
}

