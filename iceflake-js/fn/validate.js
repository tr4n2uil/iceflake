/**
 *	@module fn
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service fn_error
 *	@params key, query
 *	@result 
**/
iE.fn.error = function( $root, $sel ){
	$root.next( $sel ).stop( true, true ).slideDown( 1000 ).delay( 5000 ).fadeOut( 1000 );
	$root.focus();
	
	return true;
}

/**
 *	@service fn_email
 *	@params key, query
 *	@result 
**/
iE.fn.email = function( $index, $el ){
	var $emailRegex = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;

	if( !$emailRegex.test( $(this).val() ) ){
		iE.fn.error( $( this ), get( iE.config, 'errorsel', '.error' ) );
		return false;
	}
	
	return true;
}

/**
 *	@service fn_match
 *	@params key, query
 *	@result 
**/
iE.fn.match = ( function(){
	var $value = false;

	return function( $index, $el ){
		if( $index && $value && ( $( this ).val() != $value ) ){
			iE.fn.error( $( this ), get( iE.config, 'errorsel', '.error' ) );
			return false;
		}
		
		$value = $( this ).val();
	}
} )();

/**
 *	@service fn_required
 *	@params key, query
 *	@result 
**/
iE.fn.required = function( $index, $el ){
	if( $( this ).val() == '' ){
		iE.fn.error( $( this ), get( iE.config, 'errorsel', '.error' ) );
		return false;
	}
}

