/**
 *	@module io
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service io_dom
 *	@params key, query
 *	@result 
**/
iE.io.dom = function( $in ){
	var $root = get( $in, 'root', false, ' @io.dom' );

	if( $( $root ).is( 'form' ) ){
		$form = $( $root );
		$in[ 'url' ] = $form.attr( 'action' );
		try {
			$in[ 'request' ] = $form.attr( 'method' ).toUpperCase();
		} 
		catch( $e ) { 
			$in[ 'request' ] = 'POST'; 
		}
		
		var $params = $form.serialize();
		if( get( $in, 'ts', false ) ){
			var $d= new Date();
			$params = $params + '&_ts=' +  $d.getTime();
		}
	}
	else {
		var $params = '_ts=ts';

		if( get( $in, 'ts', false ) ){
			var $d = new Date();
			$params = '_ts=' +  $d.getTime();
		}
		
		var serialize = function( $index, $el ){
			if( $( this ).attr( 'name' ) || false ){
				$params = $params + '&' + $( this ).attr( 'name' ) + '=' +  $( this ).val();
			}
		}

		$( $root + ' ' + get( $in, 'sel', false, ' @io.dom' ) ).each( serialize );
	}

	var $data = $params.replace( /\+/g, '%20' );
	var $type = get( $in, 'type', 'url' );
	
	if( $type != 'url' ){
		var $params = $data.split( '&' );
		var $result = {};
		
		for( var $i=0, $len = $params.length; $i < $len; $i++ ){
			var $prm = ( $params[ $i ] ).split( '=' );
			$result[ $prm[ 0 ] ] = unescape( $prm[ 1 ] );
		}
		
		$data = $result;
	}
	
	switch( $type ){
		case 'json' :
			$in[ 'data' ] = JSON.stringify( $data );
			$in[ 'mime' ] =  'application/json';
			break;

		case 'data':
			$in[ 'data' ] = $data;
			$in[ 'mime' ] = 'application/data';
			break;

		case 'url' :
		default :
			$in[ 'data' ] = $data;
			$in[ 'mime' ] = 'application/x-www-form-urlencoded';
			break;
	}

	return success( $in, 'Valid DOM IO' );
}

