<?php

require_once( '../init.php' );
require_once( IEROOT.'io/std.php' );

$iconfig[ 'ns' ] = array(
	'map' => 'conf'
);

$iconfig[ 'mappings' ] = array(
	'data' => array( IEROOT.'fs/fiber.php', 'fr_data', array( 'type', 'key', 'data' ), array( 'path' => 'fiber' ) ),
	'all' => array( IEROOT.'fs/fiber.php', 'fr_all', array( 'type', 'key' ), array( 'path' => 'fiber' ) ),
	'wave' => array( IEROOT.'fs/fiber.php', 'fr_wave', array( 'key', 'wave' ), array( 'path' => 'fiber' ) ),
	'new' => array( IEROOT.'fs/fiber.php', 'fr_new', array( 'type' ), array( 'path' => 'fiber' ) ),
);

$iconfig[ 'jn' ] = array(
	'bfactor' => 5,
	// 'maxfsize' => 15 @tested
);

$iconfig[ 'die' ] = false;

/*
require_once( IEROOT.'fs/fiber.php' );
echo json_encode( tl_data( array(
	'path' => 'fiber',
	'type' => 'node',
	'key' => 'jkl',
	'data' => array( 'name' => 'jkl', 'type' => 'node', 'title' => 'JKL' )
) ) )."\n";

echo json_encode( tl_data( array(
	'path' => 'fiber',
	'type' => 'edge',
	'key' => 'abc-jkl',
	'data' => array( 'name' => 'abc-jkl', 'type' => 'edge', 'src' => 'abc', 'sink' => 'jkl' )
) ) )."\n";

exit();
*/

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
