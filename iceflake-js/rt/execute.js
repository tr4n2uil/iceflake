/**
 *	@module rt
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service rt_execute
 *	@params key, query
 *	@result 
**/
iE.rt.execute = function( $in ){
	var $idef = get( $in, 'idef', false, '@rt.execute' );

	switch( typeof $idef ){
		case 'object':
			if( get( $idef, 'service', false ) ){
				var $res = iE.rt.message( $in );
			}
			else {
				var $res = iE.rt.workflow( $in );	
			}
			break;
		case 'string':
			var $res = iE.rt.navigator( $in );
			break;
		default:
			return fail( 'Invalid iDef' );
			break;
	}
	
	return success( { result: $res }, 'Valid Runtime Execution' );
}
