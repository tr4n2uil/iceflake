/**
 *	@module fn
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service fn_invoke
 *	@params key, query
 *	@result 
**/
iE.fn.invoke = function( $in, $data, $request, $status ){
	$in[ 'data' ] = $data;
	var $valid = $in[ 'valid' ] == undefined ? true : $in[ 'valid' ];
	var $cache = get( $in, 'cache-key', false );

	var $workflow = get( $in, 'workflow', false );
	if( $workflow ){
		$in[ 'idef' ] = $workflow;
		$in = iE.rt.execute( $in );
		if( !$in[ 'result' ][ 'valid' ] ){
			die( JSON.stringify( $in ) );
			return false;
		}
	}
	else {
		var $res = iE.fn.trigger.apply( { 'data-trigger': $( $in[ 'root' ] ).attr( 'data-workflow' ), 'in': $in } );
		if( !$res ){
			die( JSON.stringify( $in ) );
			return false;
		}
	}
	
	if( $valid && $cache ){
		iE.state[ $cache ] = $data;
	}
	
	return true;
}
