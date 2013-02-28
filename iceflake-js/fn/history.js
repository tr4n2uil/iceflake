/**
 *	@module fn
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service fn_history
 *	@params
 *	@result 
**/
iE.fn.history = function( $root, $fn ){
	var $history = $( $root ).attr( 'data-history' );
	if( $history || false ){
		var $url = $( $root ).attr( $history );

		try {
			window.history.pushState( { 
				fn : $fn,
				root : $( $root ).attr( 'id' ) || iE.config.historyroot
			}, "", $url );
		} 
		catch( $id ){
			die( 'Exception : ' + $id + ' @fn.history' );
			return false;
		}
	}
	
	return true;
}

/**
 *	@service fn_onhistory
 *	@params
 *	@result 
**/
iE.fn.onhistory = function(){
	window.onpopstate = function( $data ){
		if( $data && $data.state ){
			var $fn = get( iE.fn, $data.state[ 'fn' ], iE.fn.trigger );
			$fn.apply( $( '#' + $data.state[ 'root' ] ) );
		}
	};
}
