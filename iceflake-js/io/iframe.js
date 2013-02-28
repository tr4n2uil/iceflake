/**
 *	@module io
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service io_iframe
 *	@params key, query
 *	@result 
**/
iE.io.iframe = function( $in ){
	var $mem = {};
	for( var $i in $in ){
		$mem[ $i ] = $in[ $i ];
	}

	/**
	 *	Genarate unique framename
	**/
	var $d= new Date();
	var $framename = 'firespark_iframe_' + $d.getTime();
	
	/**
	 *	Set target attribute to framename in agent
	**/
	$( get( $in, 'agent', false, '@io.iframe' ) ).attr( 'target', $framename );
	
	/**
	 *	Create IFRAME and define callbacks
	**/
	var $iframe = $( '<iframe id="' + $framename + '" name="'+ $framename + '" style="width:0;height:0;border:0px solid #fff;"></iframe>' )
		.insertAfter( iE.config.iframe_root || get( $in, 'agent', false, '@io.iframe') )
		.bind( 'load', function(){
			try {
				var $msg = iE.ui.frame( { 'name': $framename } );
				if( !$msg[ 'valid' ] ) return $msg;

				var $frame = $msg[ 'frame' ];
				var $data = $frame.document.body.innerHTML;

				switch( get( $in, 'type', false, 'json' ) ){
					case 'html' :
						//$data = $data;
						break;
					case 'json' :
					default :
						try {
							$data = $( $data ).html();
						}
						catch( $id ) { 
							die( 'Exception : ' + $id + ' @io.iframe' );
						}
						$data = $.parseJSON( $data );
						break;
				}
				
				/**
				 *	Invoke FN
				**/
				iE.fn[ get( $mem, 'fn', 'invoke' ) ]( $mem, $data, {}, 'success' );

			}
			catch( $error ){
				iE.fn[ get( $mem, 'fn', 'invoke' ) ]( $mem, $error.description, {}, 'exception' );
			}
		})
		.bind('error', function($error){
			iE.fn[ get( $mem, 'fn', 'invoke' ) ]( $mem, $error, {}, 'error' );
		});
		
	/**
	 *	Remove IFRAME after timeout (150 seconds)
	**/
	window.setTimeout(function(){
		$iframe.remove();
	}, 150000);
	
	/**
	 *	@return true 
	 *	to continue default browser event with target on iframe
	**/
	return success( {}, 'Valid iFrame IO' );
}

