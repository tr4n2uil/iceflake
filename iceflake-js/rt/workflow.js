/**
 *	@module rt
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service rt_workflow
 *	@params key, query
 *	@result 
**/
iE.rt.workflow = function( $in ){
	var $idef = get( $in, 'idef', false, '@rt.workflow' );

	if( $in[ 'valid' ] == undefined ) 
		$in[ 'valid' ] = true;
	
	var $state = $in[ 'valid' ];
	var $hops = {};
	var $hop = 0;

	var $len = $idef.length;
	while( $hop < $len ) {
		var $msg = $idef[ $hop ];
		var $role = ( $msg[ 'role' ] != undefined ) ? $msg[ 'role' ] : true;

		if( $.isEmptyObject( $hops ) && ( $role != $state ) ){
			$hop++;
			continue;
		}

		$in[ 'idef' ] = $msg;
		$in = iE.rt.message( $in );

		$state = $in[ 'valid' ];
		$hops = get( $msg, 'hops', {} );
		$hop = get( $hops, $state, $hop + 1 );
		delete $in[ 'idef' ];
	}
	


	/*for( var $i in $idef ){
		var $msg = $idef[ $i ];
		if( !$in[ 'valid' ] && get( $msg, 'nostrict', false ) ){
			continue;
		}

		$in[ 'idef' ] = $msg;
		$in = iE.rt.message( $in );
		delete $in[ 'idef' ];
	}*/

	if( !$in[ 'valid' ] )
		return $in;

	return success( $in, 'Valid Workflow Execution' );
}

