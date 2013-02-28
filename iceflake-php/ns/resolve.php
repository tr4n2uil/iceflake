<?php

/**
 *	@module ns
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service ns_resolve
 *	@params key, query
 *	@result 
**/
function ns_resolve( $in ){
	global $iconfig;

	$nsconf = get( $iconfig, get( $in, 'key', 'ns' ), false, '@ns.resolve.ns_resolve' );
	$nsmap = $nsconf[ 'map' ];

	require_once( IEROOT."ns/$nsmap.php" );

	$msg = ns_map( $in );
	if( !$msg[ 'valid' ] ) return $msg;

	return success( $msg, 'Valid Resolve Execution' );
}



?>
