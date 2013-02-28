<?php

/**
 *	@module rt
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

require_once( IEROOT.'rt/message.php' );

/**
 *	@service rt_workflow
 *	@params key, query
 *	@result 
**/
function rt_workflow( $in ){
	$idef = get( $in, 'idef', false, '@rt.workflow.rt_workflow' );

	if( !isset( $in[ 'valid' ] ) ) 
		$in[ 'valid' ] = true;

	$state = $in[ 'valid' ];
	$hops = array();
	$hop = 0;

	$len = count( $idef );
	while( $hop < $len ) {
		$msg = $idef[ $hop ];

		if( !$hops && ( get( $msg, 'role', true ) != $state ) ){
			$hop++;
			continue;
		}

		$in[ 'idef' ] = $msg;
		$in = rt_message( $in );

		$state = $in[ 'valid' ];
		$hops = get( $msg, 'hops', array() );
		$hop = get( $hops, $state, $hop + 1 );
		unset( $in[ 'idef' ] );
	}
	
	/*foreach( $idef as $msg ){
		if( !$in[ 'valid' ] && get( $msg, 'strict', true ) )
			continue;

		$in[ 'idef' ] = $msg;
		$in = rt_message( $in );
		unset( $in[ 'idef' ] );
	}*/

	if( !$in[ 'valid' ] )
		return $in;
		
	return success( $in, 'Valid Workflow Execution' );
}


?>
