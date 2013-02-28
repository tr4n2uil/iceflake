<?php

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
function rt_execute( $in ){
	$idef = get( $in, 'idef', false, '@rt.execute.rt_execute' );

	switch( gettype( $idef ) ){
		case 'array':
			if( get( $idef, 'service', false ) ){
				require_once( IEROOT.'rt/message.php' );
				$res = rt_message( $in );
			}
			else {
				require_once( IEROOT.'rt/workflow.php' );
				$res = rt_workflow( $in );	
			}
			break;
		case 'string':
			require_once( IEROOT.'rt/navigator.php' );
			$res = rt_navigator( $in );
			break;
		default:
			return fail( 'Invalid iDef' );
			break;
	}
	
	return success( array( 'result' => $res ), 'Valid Runtime Execution' );
}



?>
