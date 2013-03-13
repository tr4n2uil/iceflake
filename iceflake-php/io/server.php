<?php

/**
 *	@module server
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

require_once( IEROOT. 'fn/trigger.php' );

/*declare(ticks = 1);
pcntl_signal( SIGCHLD, "signal_handler" );

function signal_handler( $signal ){
	echo "Receiving Signal\n";
	switch( $signal ){
		case SIGCHLD:
			while( pcntl_waitpid( 0, $status ) != -1 ){
				$status = pcntl_wexitstatus( $status );
				echo "Child $status Completed\n";
			}
			exit();
    }
}*/

function io_server( $in ){
	$family = get( $in, 'family', 'unix' );
	switch( $family ){
		case 'ipv4':
			$family = AF_INET;
			break;
		case 'ipv6':
			$family = AF_INET6;
			break;
		case 'unix':
		default:
			$family = AF_UNIX;
			break;
	}

	$type = 0;
	$listen = false;
	switch( get( $in, 'type', 'raw' ) ){
		case 'stream':
			$type = SOCK_STREAM;
			$listen = true;
			break;
		case 'dgram':
			$type = SOCK_DGRAM;
			break;
		case 'packet':
			$type = SOCK_SEQPACKET;
			$listen = true;
			break;
		case 'raw':
		default:
			$type = SOCK_RAW;
			break;
	}

	$socket = socket_create( $family, $type, getprotobyname( get( $in, 'protocol', 'ip' ) ) );
	if( $socket === false ) {
	    $errorcode = socket_last_error();
	    fail( "Couldn't create socket: [$errorcode] ". socket_strerror( $errorcode ) );
	}

	$address = get( $in, 'address', '/tmp/iceflake' );
	$port = get( $in, 'port', 0 );
	if( !socket_bind( $socket, $address, $port ) ){
		$errorcode = socket_last_error();
	    fail( "Couldn't bind socket: [$errorcode] ". socket_strerror( $errorcode ) );
	}

	if( $listen && !socket_listen( $socket ) ){
		$errorcode = socket_last_error();
	    fail( "Couldn't listen socket: [$errorcode] ". socket_strerror( $errorcode ) );
	}

	socket_set_block( $socket );
	$timeout = get( $in, 'timeout', null );
	if( $timeout ){
		socket_set_option( $socket, SOL_SOCKET, SO_SNDTIMEO, array( "sec" => $timeout, "usec" => 0 ) );
		socket_set_option( $socket, SOL_SOCKET, SO_RCVTIMEO, array( "sec" => $timeout, "usec" => 0 ) );
		socket_set_option( $socket, SOL_SOCKET, SO_LINGER, array( "l_onoff" => 1, "l_linger" => 1 ) );
	}

	echo "[".date("Y-m-d H:i:s")."] [$address:$port] IO Server Started\n";
	
	$end = time() + $timeout;
	$r = array( $socket );
	$w = null;
	$e = null;

	if( $timeout && !socket_select( $r, $w, $e, $timeout ) )
		die( "[".date("Y-m-d H:i:s")."] [$address:$port] Server Timeout Error\n" );
	
	while( $conn = socket_accept( $socket ) ) {
		socket_getpeername( $conn, $address, $port );
		$pid = pcntl_fork();

		if( $pid == -1 ){
        	echo "[".date("Y-m-d H:i:s")."] [$address:$port] Fork Failure\n";
        	die( 'Error Forking Child Process' );
    	}
    	elseif( $pid == 0 ){
    		$pid2 = pcntl_fork();
    		if( $pid2 == -1 ){ 
	        	die( 'Error Forking Second Child Process' );
	    	}
	    	elseif( $pid2 == 0 ){
	    		socket_close( $socket ); 

	        	$r = array( $conn );
	        	$w = null;
	        	$e = null;
	        	if( $timeout && !socket_select( $r, $w, $e, $end - time() ) ){
	        		socket_close( $conn );  
	        		die( "[".date("Y-m-d H:i:s")."] [$address:$port] Connection Timeout Error\n" );
	        	}
	        	
	        	$data = '';
				$buf = socket_read( $conn, 1024, PHP_NORMAL_READ );
				$buf .= socket_read( $conn, 1 );

				while( $buf != "" && $buf[ 0 ] != "\r" ){
					$data .= $buf;

					if( $timeout && !socket_select( $r, $w, $e, $end - time() ) ){
		        		socket_close( $conn );  
		        		die( "[".date("Y-m-d H:i:s")."] [$address:$port] Connection Timeout Error\n" );
		        	}

					$buf = socket_read( $conn, 1024, PHP_NORMAL_READ );
					$buf .= socket_read( $conn, 1 );
				}
				
				$data = trim( $data );
				echo "[".date("Y-m-d H:i:s")."] [$address:$port] $data\n";

				$in[ 'data' ] = $data;
				$res = call_user_func( get( $in, 'fn', 'fn_trigger' ), $in );
				//socket_write( $conn, "HTTP/1.1 200 OK\nServer: Apache/2.2.22 (Unix) DAV/2 PHP/5.3.15 with Suhosin-Patch\nLast-Modified: Thu, 28 Feb 2013 07:31:25 GMT\nContent-Type: text/html\n\nDATA\n\n"/*.$res*/ );
				socket_write( $conn, $res );

				@socket_shutdown( $conn, 2 );
				socket_close( $conn );  

				exit();
	    	}
	    	else {
	    		exit();
	    	}
    	}
    	else { 
    		socket_close( $conn ); 
    		pcntl_waitpid( $pid, $status );
    		$time = time();

        	if( $timeout && ( $time > $end ) )
				break;

        	if( $timeout && !socket_select( $r, $w, $e, $end - $time ) ){
        		echo "[".date("Y-m-d H:i:s")."] [$address:$port] Server Timeout Error\n";
        		break;
        	}
		}
	}

	@socket_shutdown( $socket, 1 );
	usleep( 500000 );
	socket_close( $socket );

	if( $family == AF_UNIX )
		unlink( $address );

	return $in;
}


?>
