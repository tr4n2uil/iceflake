/**
 *	@module ui
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service ui_content
 *	@params
 *	@result 
**/
iE.ui.content = function( $in ){
	var $el = get( $in, 'el', get( $in, 'root', false ) );

	if( $el ){
		$el = $( $el );
		$in[ 'olddata' ] = $( $el ).html();
		var $data = get( $in, 'data', iE.config.loaderhtml );

		var $anm = get( $in, 'anm', 'none' );
		var $dur = get( $in, 'dur', 0 );
		var $act = get( $in, 'act', 'all' );
		
		if( $.isPlainObject( $data ) && $in[ 'act' ] != 'remove' && $data[ 'html' ] || false){
			$data = $( "<div/>" ).html( $data[ 'html' ] ).text();
		}

		if( $anm == 'fadein' || $anm == 'slidein' ){
			$el.hide();
		}
		
		switch( $act ){
			case 'all' :
				$el = $el.html( $data );
				$el.trigger( 'load' );
				break;
			
			case 'first' :
				$el = $el.prepend( $data );
				$el.trigger( 'load' );
				break;
			
			case 'last' :
				$el = $el.append( $data );
				$el.trigger( 'load' );
				break;
			
			case 'replace' :
				$el = $( $data ).replaceAll( $element );
				$el.trigger( 'load' );
				break;
				
			case 'remove' :
				$el.remove();
				break;
				
			default :
				break;
		}
		
		if( $act != 'remove' ){
			$el.stop( true, true ).delay( get( $in, 'delay', 0 ) );
			
			switch( $anm ){
				case 'fadein' :
					$el.fadeIn( $dur );
					break;
				case 'fadeout' :
					$el.fadeOut( $dur );
					break;
				case 'slidein' :
					$el.slideDown( $dur );
					break;
				case 'slideout' :
					$el.slideUp( $dur );
					break;
				case 'none' :
					break;
				default :
					$el.html( 'Animation type not supported' ).fadeIn( $dur );
					break;
			}
		}
		
		$in[ 'element' ] = $el;
	}

	return success( $in, 'Valid Content UI' );
}

