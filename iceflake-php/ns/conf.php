<?php

/**
 *	@module ns
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service ns_map
 *	@params key, query
 *	@result 
**/
function ns_map( $in ){
	global $iconfig;

	$query = get( $in, 'query', false, '@ns.conf.ns_map' );
	$nsconf = get( $iconfig, get( $in, 'key', 'ns' ), false, '@ns.conf.ns_map' );

	$mappings = get( $iconfig, get( $nsconf, 'map.key', 'mappings' ), get( $nsconf, 'map.default', false ), '@ns.conf.ns_map' );
	$res = get( $mappings, $query, false );

	if( $res === false )
		return fail( 'NS Mapping Not Found', 'Query: '.$query );
	
	return success( array( 'mapping' => $res ), 'Valid Conf Map Execution' );
}


?>
