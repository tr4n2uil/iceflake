/**
 *	@module ui
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service ui_template
 *	@params
 *	@result 
**/
iE.ui.template = function( $in ){
	if( get( $in, 'tpl', false ) ){
		$tpl = $in[ 'tpl' ];
		$template = get( iE.state, $in[ 'tpl' ], false );

		if( !$template && $tpl.charAt( 0 ) == '#' ){
			$template = $.template( $tpl );
			if( $template ){
				set( iE.state, $in[ 'tpl' ], $template );
				$tpl = $template;
			}
		}

		$in[ 'result' ] = $.tmpl( $tpl, get( $in, 'data', {} ) );
	}
	else {
		return fail( 'Template Key Not Found' );
	}

	return success( $in, 'Valid Template UI' );
}

