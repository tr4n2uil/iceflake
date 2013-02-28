/**
 *	@module io
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service io_event
 *	@params key, query
 *	@result 
**/
iE.io.event = function( $in ){
	$( get( $in, 'root', document ) ).on( 
		get( $in, 'event', 'click' ), 
		get( $in, 'sel', '.trigger' ), 
		get( $in, 'fn', iE.fn.trigger ) 
	);

	return success( {}, 'Valid Event IO' );
}
