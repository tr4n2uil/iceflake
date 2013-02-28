/**
 *	@module fn
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service fn_collect
 *	@params key, query
 *	@result 
**/
iE.fn.collect = function( event ){
	var $block = $( this ).parent();
	var $in = { root : $( this ) };
	
	var serialize = function( $index, $el ){
		if( $( this ).attr( 'name' ) || false ){
			$in[ $( this ).attr( 'name' ) ] =  $( this ).val();
		}
	}

	$block.children( 'input' ).each( serialize );

	iE.fn.history( this, 'collect' );

	$in = iE.rt.execute( $in );
	if( !$in[ 'valid' ] ){
		die( JSON.stringify( $in ) );
		return false;
	}
	
	return true;
}
