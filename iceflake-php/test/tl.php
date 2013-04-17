<?php

require_once( '../init.php' );
require_once( IEROOT.'io/std.php' );

$iconfig[ 'ns' ] = array(
	'map' => 'conf'
);

$iconfig[ 'mappings' ] = array(
	'data' => array( IEROOT.'fs/traveller.php', 'tl_data', array( 'type', 'key', 'data' ), array( 'path' => 'traveller' ) ),
	'all' => array( IEROOT.'fs/traveller.php', 'tl_all', array( 'type', 'key' ), array( 'path' => 'traveller' ) ),
	'new' => array( IEROOT.'fs/traveller.php', 'tl_new', array( 'type' ), array( 'path' => 'traveller' ) ),
);

$iconfig[ 'jn' ] = array(
	'bfactor' => 5,
	// 'maxfsize' => 15 @tested
);

$iconfig[ 'die' ] = false;

/*
require_once( IEROOT.'fs/traveller.php' );
echo json_encode( tl_data( array(
	'path' => 'traveller',
	'type' => 'node',
	'key' => 'ghi',
	'data' => array( 'name' => 'ghi', 'type' => 'node', 'title' => 'GHI' )
) ) )."\n";

echo json_encode( tl_data( array(
	'path' => 'traveller',
	'type' => 'edge',
	'key' => 'abc-ghi',
	'data' => array( 'name' => 'abc-ghi', 'type' => 'edge', 'src' => 'abc', 'sink' => 'ghi' )
) ) )."\n";

exit();*/

require_once( IEROOT. 'io/server.php' );
io_server( array(
	'family' => 'ipv4',
	'type' => 'stream',
	'protocol' => 'tcp',
	'address' => get( $argv, 2, '127.0.0.1' ),
	'port' => get( $argv, 3, '8080' ),
	'timeout' => get( $argv, 1, null )
) );


?>
