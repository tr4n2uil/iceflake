<?php

require_once( 'init.php' );
require_once( 'io/std.php' );

$iconfig[ 'db' ] = array(
	'type' => 'mysqli',
	'user' => 'root',
	'pass' => 'krishna',
	'host' => 'localhost',
	'database' => 'mysql'
);

//require_once( 'db/query.php' );
//std_output( db_query( array( 'query' => 'select * from user;' ) ) );

$iconfig[ 'ns' ] = array(
	'map' => 'conf'
);

$iconfig[ 'mappings' ] = array(
	'dbtest' => array( IEROOT.'db/query.php', 'db_query', array( 'query' ) )
);

//require_once( 'ns/resolve.php' );
//std_output( ns_resolve( array( 'query' => 'dbtest' ) ) );

require_once( 'rt/execute.php' );
/*std_output( rt_execute( array(
 	'idef' => array( 
 		array( 
 			'service' => 'dbtest', 
 			'input' => array( 'qry' => 'query' ), 
 			'output' => array( 'result' => 'result01' ),
 			'hops' => array( true => 1, false => 1 )
 		), 
 		array( 
 			'service' => 'dbtest', 
 			//'input' => array( 'qry' => 'query' ), 
 			'output' => array( 'result' => 'result02' ),
 			'role' => false,
			'query' => 'select * from user;'
 		) 
 	), 
 	'qry' => 'select * from users;' 
) ) );*/

//std_output( rt_execute( array( 'idef' => 'dbtest/select * from user/~/output/{result:res1}' ) ) );
std_output( rt_execute( array( 'idef' => 'dbtest/select * from user/~/output/{result:res1,count:cnt}/-/dbtest/~/query/select * from help_keyword limit 5/output/{result:res2}/' ) ) );


/* Simple Server Controller

http_output( array(
	'type' => 'json',
	'data' => rt_execute( 
		array( 'idef' => http_input( array( 'type' => 'qrystr' ) ) ) 
	)
) );

*/

?>
