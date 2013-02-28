/**
 *	@module rt
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service rt_message
 *	@params key, query
 *	@result 
**/
iE.rt.message = function( $in ){
	var $idef = get( $in, 'idef', false, '@rt.message' );

	var $service = get( $idef, 'service', false, '@rt.message' );

	if( typeof $service == 'string' ){
		var $mapping = get( get( iE.config, 'mappings', false, '@rt.message' ), $service, false );
	}
	else {
		var $mapping = [ $service ]
	}
	
	var $service = $mapping[ 0 ];
	var $output = get( $idef, 'output', {} );

	// args
	var $args = get( $idef, 'args', [] );
	for( var $key in $args ) {
		$key = $args[ $key ];
		$idef = set( $idef, $key, get( $in, $key, false ) );
	}

	// set
	var $set = get( $mapping, 1, [] );
	for( var $key in $set ) {
		var $value = $set[ $key ];
		var $val = get( $in, $key, false );
		if( $val ){
			$idef = set( $idef, $value, $val );
		}
	}

	// input
	var $input = get( $idef, 'input', {} );
	for( var $key in $input ) {
		var $value = $input[ $key ];
		$idef = set( $idef, $value, get( $in, $key, false ) );
	}

	// run
	$in[ 'idef' ] = $idef = $service( $idef );
	if( !$idef[ 'valid' ] ){
		return fail( $idef[ 'msg' ], $idef[ 'details' ] );
	}

	// output
	for( var $key in $output ) {
		var $value = $output[ $key ];
		$in = set( $in, $value, get( $idef, $key, false ) );
	}

	return success( $in, 'Valid Message Execution' );
}
