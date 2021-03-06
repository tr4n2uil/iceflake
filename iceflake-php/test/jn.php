<?php

require_once( '../init.php' );
require_once( IEROOT.'io/std.php' );

$iconfig[ 'ns' ] = array(
	'map' => 'conf'
);

$iconfig[ 'mappings' ] = array(
	//'jncr' => array( IEROOT.'jn/control.php', 'jn_create', array( 'path' ) ),
	'data' => array( IEROOT.'fs/journal.php', 'jn_data', array( 'name', 'key', 'data' ), array( 'path' => 'db' ) ),
	'all' => array( IEROOT.'fs/journal.php', 'jn_all', array( 'name', 'key', 'data' ), array( 'path' => 'db' ) ),
	'new' => array( IEROOT.'fs/journal.php', 'jn_new', array( 'name', 'chunksize' ), array( 'path' => 'db' ) ),
	'io' => array( IEROOT.'io/server.php', 'io_server', array( 'port' ), array( 'family' => 'ipv4', 'type' => 'stream', 'protocol' => 'tcp', 'address' => get( $argv, 1, '127.0.0.1' ) ) )
);

$iconfig[ 'jn' ] = array(
	'bfactor' => 5,
	// 'maxfsize' => 15 @tested
);

$iconfig[ 'die' ] = false;

require_once( IEROOT. 'io/server.php' );
io_server( array(
	'family' => 'ipv4',
	'type' => 'stream',
	'protocol' => 'tcp',
	'address' => get( $argv, 2, '127.0.0.1' ),
	'port' => get( $argv, 3, '8080' ),
	'timeout' => get( $argv, 1, null )
) );

//require_once( IEROOT.'fn/trigger.php' );

//echo fn_trigger( array( 'data' => 'get/kanna/~/output/{data:result}' ) );

/*std_output( rt_execute( array(
 	'idef' => array( 
 		array(
 			'service' => 'jnlu',
 			'input' => array( 'path' => 'path', 'ky' => 'key' ), 
 			'output' => array( 'block' => 'block', 'parent' => 'parent' )
 		),
 		array(
 			'service' => 'jnsp',
 			'input' => array( 'path' => 'path', 'block' => 'block', 'ky' => 'key' ),
 		),
 		array(
 			'service' => 'jnap',
 			'input' => array( 'path' => 'path', 'block' => 'block', 'ky' => 'key', 'data' => 'data' ), 
 		),
 		array(
 			'service' => 'jnlu',
 			'input' => array( 'path' => 'path', 'ky' => 'key' ), 
 			'output' => array( 'block' => 'block', 'parent' => 'parent' )
 		),
 		array( 
 			'service' => 'jnrd',
 			'input' => array( 'path' => 'path', 'block' => 'block', 'ky' => 'key' ), 
 			'output' => array( 'data' => 'qdata' )
 		)
 		/*array(
 			'service' => 'jnlu',
 			'insert' => true,
 			'input' => array( 'path' => 'path', 'ky' => 'key' ), 
 			'output' => array( 'block' => 'block', 'parent' => 'parent' )
 		),
 	),
 	'path' => IDROOT. 'db/test',
 	'ky' => 'unnikanna',
 	'data' => 'ponnukanna'
) ) );

/*
require_once( IDROOT. 'jn/read.php' );

echo json_encode( jn_read( array(
	'path' => 
	'block' => 'abc',
	'key' => 'abc',
	//'head' => array( 'abc' => array( 28, 7 ), '_order' => array( 'abc' ) ),
) ) );
*/
?>
