// Initialize iceFlake JS

var iE = {
   fn: {},
   io: {},
   rt: {},
   ui: {},
   si: {},
   config: {},
   state: {}   
};

function die( $str ){
   if( console || false ){
      console.log( $str );
   }
   else {
      alert( 'Error: ' + $str );
   }
}

function fail( $msg, $details, $die ){
   var $result = {
      valid: false,
      msg: $msg,
      details: $details || ''
   };

   if( $die || false ){
      die( JSON.stringify( $result ) + ' ' + $die );
   }
   return $result;
}

function success( $result, $msg, $details ){
   $result = $result || {};
   $result[ 'valid' ] = $result[ 'valid' ] || true;
   $result[ 'msg' ] = $msg || 'Successfully Executed';
   $result[ 'details' ] = $details || 'Successfully Executed';
   return $result;
}

function get( $map, $key, $def, $die ){
   if( ( $die || false ) && !( $map[ $key ] || false ) ){
      die( 'Unable to find key in map: ' + $key + " " + $die + "\n" );
   }
   return $map[ $key ] || $def || false;
} 

function set( $map, $key, $value, $die ){
   if( $map[ $key ] || false ){
      if( $die || false ){
         die( 'Unable to set key in map: ' + $key + " " + $die + "\n" );
      }
      else {
         return $map;
      }
   }

   $map[ $key ] = $value;
   return $map;
}

function isNumber( $n ) {
	return !isNaN( parseFloat( $n ) ) && isFinite( $n );
}

function is_numeric( $n ){
	return !isNaN( Number( $n ) );
}

function unique( $array ){
   var u = {}, a = [];
   for( var i = 0, l = $array.length; i < l; ++i ){
      if( $array[ i ] in u )
         continue;
      a.push( $array[ i ] );
      u[ $array[ i ] ] = 1;
   }
   return a;
}
/**
 *	@module io
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service io_ajax
 *	@params key, query
 *	@result 
**/
iE.io.ajax = function( $in ){
	var $mem = {};
	for( var $i in $in ){
		$mem[ $i ] = $in[ $i ];
	}
	
	/**
	 *	Load data from server using AJAX
	**/
	$.ajax({
		url: get( $in, 'url', false, '@io.ajax'),
		data: get( $in, 'data', '' ),
		dataType : get( $in, 'type', 'json' ),
		type : get( $in, 'request', 'POST' ),
		processData : get( $in, 'process', false ),
		contentType : get( $in, 'mime', 'application/x-www-form-urlencoded' ),
		async : !get( $in, 'sync', false ),
		
		success : function( $data, $status, $request ){
			iE.fn[ get( $mem, 'fn', 'invoke' ) ]( $mem, $data, $request, $status );
		},
		
		error : function( $request, $status, $error ){
			$mem[ 'valid' ] = false;
			iE.fn[ get( $mem, 'fn', 'invoke' ) ]( $mem, $error, $request, $status );
		}
	});
	
	/**
	 *	@return false 
	 *	to stop default browser event
	**/
	if( !get( $in, 'nostop', false ) ){
		return fail( 'Event Propagation Stopped for AJAX' );
	}

	return success( {}, 'Valid AJAX IO' );
}

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

/**
 *	@module io
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service io_event
 *	@params key, query
 *	@result 
**/
iE.io.event = function( $in ){
	$in = $in || {};
	$( get( $in, 'root', document ) ).on( 
		get( $in, 'event', 'click' ), 
		get( $in, 'sel', '.trigger' ), 
		get( $in, 'fn', iE.fn.trigger ) 
	);

	return success( {}, 'Valid Event IO' );
}
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
iE.rt.execute = function( $in ){
	var $idef = get( $in, 'idef', false, '@rt.execute' );

	switch( typeof $idef ){
		case 'object':
			if( get( $idef, 'service', false ) ){
				var $res = iE.rt.message( $in );
			}
			else {
				var $res = iE.rt.workflow( $in );	
			}
			break;
		case 'string':
			var $res = iE.rt.navigator( $in );
			break;
		default:
			return fail( 'Invalid iDef' );
			break;
	}
	
	return success( { result: $res }, 'Valid Runtime Execution' );
}
/**
 *	@module rt
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service rt_message
 *	@params key, query
 *	@result 
**/
iE.rt.message = function( $in ){
	var $idef = get( $in, 'idef', false, '@rt.message' );

	var $service = get( $idef, 'service', false, '@rt.message' );

	if( typeof $service == 'string' ){
		var $mapping = get( get( iE.config, 'mappings', false, '@rt.message' ), $service, false );
	}
	else {
		var $mapping = [ $service ]
	}
	
	var $service = $mapping[ 0 ];
	var $output = get( $idef, 'output', {} );

	// args
	var $args = get( $idef, 'args', [] );
	for( var $key in $args ) {
		$key = $args[ $key ];
		$idef = set( $idef, $key, get( $in, $key, false ) );
	}

	// set
	var $set = get( $mapping, 1, [] );
	for( var $key in $set ) {
		var $value = $set[ $key ];
		var $val = get( $in, $key, false );
		if( $val ){
			$idef = set( $idef, $value, $val );
		}
	}

	// input
	var $input = get( $idef, 'input', {} );
	for( var $key in $input ) {
		var $value = $input[ $key ];
		$idef = set( $idef, $value, get( $in, $key, false ) );
	}

	// run
	$in[ 'idef' ] = $idef = $service( $idef );
	if( !$idef[ 'valid' ] ){
		return fail( $idef[ 'msg' ], $idef[ 'details' ] );
	}

	// output
	for( var $key in $output ) {
		var $value = $output[ $key ];
		$in = set( $in, $value, get( $idef, $key, false ) );
	}

	return success( $in, 'Valid Message Execution' );
}
/**
 *	@module rt
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@helper parse
 *
**/
iE.rt.parse = function( $data ){
	switch( $data[ 0 ] ){
		case '[':
			$data = $data.substring( 1, $data.length - 1 );
			return $data.split( ',' );

		case '{':
			$data = $data.substring( 1, $data.length - 1 );
			$data = $data.split( ',' );
			var $res = {};
			for( var $i in $data ){
				var $parts = $data[ $i ].split( ':' );
				$res[ $parts[ 0 ] ] = $parts[ 1 ];
			}
			return $res;

		case 't':
			if( $data == "true" )
				return true;

		case 'f':
			if( $data == "false" )
				return false;
			
		default:
			return decodeURIComponent( $data );
	}
}

/**
 *	@service rt_navigator
 *	@params key, query
 *	@result 
**/
iE.rt.navigator = function( $in ){
	var $str = get( $in, 'idef', false, '@rt.navigator' );
	var $_in = $str.split( '/-/' );

	for( var $j in $_in ){
		$str = $_in[ $j ];

		var $idef = {};
		var $args = $str.split( '~' );

		var $path = $args[ 0 ].split( '/' );
		$idef[ 'service' ] = $path[ 0 ];
		var $max = $path.length;
		
		if( !$path[ $max - 1 ] ){
			delete $path[ --$max ];
		}
		
		for( var $i = 1; $i < $max; $i++ ){
			$in[ $i - 1 ] = $path[ $i ];
		}

		if( get( $args, 1, false ) ){
			$path = $args[ 1 ].split( '/' );
			$max = $path.length;
			
			for( var $i = 0; ( $i + 1 ) < $max; $i++ ){
				$idef[ $path[ $i ] ] = iE.rt.parse( $path[ $i + 1 ] );
			}
		}

		$_in[ $j ] = $idef;
	}
	

	$in[ 'idef' ] = $_in;
	$in = iE.rt.execute( $in );
	//unset( $in[ 'idef' ] );
	if( !$in[ 'result' ][ 'valid' ] ){
		return $in[ 'result' ];
	}

	$in = $in[ 'result' ];

	return success( $in, 'Valid Navigator Execution' );
}


/**
 *	@module rt
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service rt_workflow
 *	@params key, query
 *	@result 
**/
iE.rt.workflow = function( $in ){
	var $idef = get( $in, 'idef', false, '@rt.workflow' );

	if( $in[ 'valid' ] == undefined ) 
		$in[ 'valid' ] = true;
	
	var $state = $in[ 'valid' ];
	var $hops = {};
	var $hop = 0;

	var $len = $idef.length;
	while( $hop < $len ) {
		var $msg = $idef[ $hop ];
		var $role = ( $msg[ 'role' ] != undefined ) ? $msg[ 'role' ] : true;

		if( $.isEmptyObject( $hops ) && ( $role != $state ) ){
			$hop++;
			continue;
		}

		$in[ 'idef' ] = $msg;
		$in = iE.rt.message( $in );

		$state = $in[ 'valid' ];
		$hops = get( $msg, 'hops', {} );
		$hop = get( $hops, $state, $hop + 1 );
		delete $in[ 'idef' ];
	}
	


	/*for( var $i in $idef ){
		var $msg = $idef[ $i ];
		if( !$in[ 'valid' ] && get( $msg, 'nostrict', false ) ){
			continue;
		}

		$in[ 'idef' ] = $msg;
		$in = iE.rt.message( $in );
		delete $in[ 'idef' ];
	}*/

	if( !$in[ 'valid' ] )
		return $in;

	return success( $in, 'Valid Workflow Execution' );
}

/**
 *	@module ui
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service ui_alert
 *	@params key, query
 *	@result 
**/
iE.ui.alert = function( $in ){
	alert( get( $in, 'data', 'Krishna' ) );

	return success( {}, 'Valid Workflow Execution' );
}

/**
 *	@module ui
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service ui_attribute
 *	@params
 *	@result 
**/
iE.ui.attribute = function( $in ){
	var $el = get( $in, 'el', get( $in, 'root', false ) );
	var $attr = get( $in, 'attr', 'disabled' );
	var $value = get( $in, 'val', false );

	if( $el ){
		if( $value ){
			$( $el ).attr( $attr, $value );
		}
		else {
			$( $el ).removeAttr( $attr );
		}

		$in[ 'element' ] = $el;
	} 

	return success( $in, 'Valid Attribute UI' );
}

/**
 *	@module ui
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service ui_confirm
 *	@params key, query
 *	@result 
**/
iE.ui.confirm = function( $in ){
	if( confirm( get( $in, 'data', 'Are you sure you want to continue ?' ) ) ){
		return success( $in, 'Valid Confirm Execution' );
	}

	return fail( 'User Did Not Confirm' );
}

/**
 *	@module ui
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service ui_content
 *	@params
 *	@result 
**/
iE.ui.content = function( $in ){
	var $el = get( $in, 'el', get( $in, 'root', false ) );

	if( $el ){
		$el = $( $el );
		$in[ 'olddata' ] = $( $el ).html();
		var $data = get( $in, 'data', iE.config.loaderhtml );

		var $anm = get( $in, 'anm', 'none' );
		var $dur = get( $in, 'dur', 0 );
		var $act = get( $in, 'act', 'all' );
		
		if( $.isPlainObject( $data ) && $in[ 'act' ] != 'remove' && $data[ 'html' ] || false){
			$data = $( "<div/>" ).html( $data[ 'html' ] ).text();
		}

		if( $anm == 'fadein' || $anm == 'slidein' ){
			$el.hide();
		}
		
		switch( $act ){
			case 'all' :
				$el = $el.html( $data );
				$el.trigger( 'load' );
				break;
			
			case 'first' :
				$el = $el.prepend( $data );
				$el.trigger( 'load' );
				break;
			
			case 'last' :
				$el = $el.append( $data );
				$el.trigger( 'load' );
				break;
			
			case 'replace' :
				$el = $( $data ).replaceAll( $element );
				$el.trigger( 'load' );
				break;
				
			case 'remove' :
				$el.remove();
				break;
				
			default :
				break;
		}
		
		if( $act != 'remove' ){
			$el.stop( true, true ).delay( get( $in, 'delay', 0 ) );
			
			switch( $anm ){
				case 'fadein' :
					$el.fadeIn( $dur );
					break;
				case 'fadeout' :
					$el.fadeOut( $dur );
					break;
				case 'slidein' :
					$el.slideDown( $dur );
					break;
				case 'slideout' :
					$el.slideUp( $dur );
					break;
				case 'none' :
					break;
				default :
					$el.html( 'Animation type not supported' ).fadeIn( $dur );
					break;
			}
		}
		
		$in[ 'element' ] = $el;
	}

	return success( $in, 'Valid Content UI' );
}

/**
 *	@module ui
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service ui_cookie
 *	@params
 *	@result 
**/
iE.ui.cookie = function( $in ){
	if( get( $in, 'key', false ) ){
		$.cookie( $in[ 'key' ], null, { path: get( $in, 'path', '/' ) });
		
		$.cookie( $in[ 'key' ], get( $in, 'value', null ), {
			expires : get( $in, 'expires', 1 ),
			path: get( $in, 'path', '/' )
		});
	}
	else {
		return fail( 'Cookie Key Not Found' );
	}

	return success( $in, 'Valid Cookie UI' );
}

/**
 *	@module ui
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service ui_display
 *	@params
 *	@result 
**/
iE.ui.display = function( $in ){
	$el = get( $in, 'el', get( $in, 'root', false ) );
	
	if( $el ){
		$el = $( $el );

		switch( get( $in, 'act', 'filter' ) ){
			case 'show':
				$el.show();
				break;
			case 'hide':
				$el.hide();
				break;
			case 'toggle':
				$el.toggle();
				break;
			case 'filter':
			default:
				$sel = get( $in, 'sel', '.tile-content' );
				$tile = get( $in, 'tile', '#main-tile' )
				$el.children( $sel ).hide();
				$el.children( $tile ).show();
				break;
		}

		$in[ 'element' ] = $el;
	}

	return success( $in, 'Valid Attribute UI' );
}

/**
 *	@module ui
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service ui_frame
 *	@params key, query
 *	@result 
**/
iE.ui.frame = function( $in ){
	var $name = get( $in, 'name', false );

	for( var i = window.frames.length -1; i >= 0; i-- ){
		var $frame = window.frames[ i ];
		if( $frame.name || false ){
			if( $frame.name == $name ){
				return success( { 'frame': $frame }, 'Valid Frames Execution' );
			}
		}
	}

	return fail( 'Frame Not Found' );
}

/**
 *	@module ui
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service ui_reload
 *	@params key, query
 *	@result 
**/
iE.ui.reload = function( $in ){
	if( get( $in, 'next', false ) ){
		window.location = $in[ 'next' ];
	}
	else {
		window.location.reload();
	}

	return success( $in, 'Valid Reload Execution' );
}

/**
 *	@module ui
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service ui_scroll
 *	@params
 *	@result 
**/
iE.ui.scroll = function( $in ){
	var $el = get( $in, 'el', false );

	if( $el ){
		$( $el ).animate( { scrollTop: get( $in, 'top', 0 ) }, get( $in, 'dur', 500 ) );
	}

	return success( $in, 'Valid Template UI' );
}
/**
 *	@module ui
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service ui_template
 *	@params
 *	@result 
**/
iE.ui.template = function( $in ){
	if( get( $in, 'tpl', false ) ){
		$tpl = $in[ 'tpl' ];
		$template = get( iE.state, $in[ 'tpl' ], false );

		if( !$template && $tpl.charAt( 0 ) == '#' ){
			$template = $.template( $tpl );
			if( $template ){
				set( iE.state, $in[ 'tpl' ], $template );
				$tpl = $template;
			}
		}

		$in[ 'result' ] = $.tmpl( $tpl, get( $in, 'data', {} ) );
	}
	else {
		return fail( 'Template Key Not Found' );
	}

	return success( $in, 'Valid Template UI' );
}

/**
 *	@module ui
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service ui_trigger
 *	@params
 *	@result 
**/
iE.ui.trigger = function( $in ){
	$el = get( $in, 'el', get( $in, 'root', false ) );

	if( $el ){
		$( $el ).trigger( get( $in, 'event', 'load' ) );
		$in[ 'element' ] = $el;
	}

	return success( $in, 'Valid Trigger UI' );
}

/**
 *	@module ui
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service ui_validate
 *	@params key, query
 *	@result 
**/
iE.ui.validate = function( $in ){
	var $res = true;

	var validate = function( $index, $el ){
		var $data = $( this ).attr( 'data-validate' );
		if( $data ){
			$data = $data.split( ' ' );

			for( var $i in $data ) {
				var $fx = $res = get( iE.fn, $data[ $i ], false, '@ui.validate No fn:' + $data[ $i ] );
				if( $fx ){
					$res = $fx( $( this ) );
				}
				if( !$res ){
					return false;
				}
			};
		}
	}

	$( get( $in, 'sel', false, ' @ui.validate' ) ).each( validate );

	if( $res ){
		return success( $in, 'Valid Confirm Execution' );
	}

	return fail( 'User Did Not Confirm' );
}
/**
 *	@module fn
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service fn_collect
 *	@params key, query
 *	@result 
**/
iE.fn.collect = function( event ){
	var $block = $( this ).parent();
	var $in = { root : $( this ) };
	
	var serialize = function( $index, $el ){
		if( $( this ).attr( 'name' ) || false ){
			$in[ $( this ).attr( 'name' ) ] =  $( this ).val();
		}
	}

	$block.children( 'input' ).each( serialize );

	iE.fn.history( this, 'collect' );

	$in = iE.rt.execute( $in );
	if( !$in[ 'valid' ] ){
		die( JSON.stringify( $in ) );
		return false;
	}
	
	return true;
}
/**
 *	@module fn
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service fn_history
 *	@params
 *	@result 
**/
iE.fn.history = function( $root, $fn ){
	var $history = $( $root ).attr( 'data-history' );
	if( $history || false ){
		var $url = $( $root ).attr( $history );

		try {
			window.history.pushState( { 
				fn : $fn,
				root : $( $root ).attr( 'id' ) || iE.config.historyroot
			}, "", $url );
		} 
		catch( $id ){
			die( 'Exception : ' + $id + ' @fn.history' );
			return false;
		}
	}
	
	return true;
}

/**
 *	@service fn_onhistory
 *	@params
 *	@result 
**/
iE.fn.onhistory = function(){
	window.onpopstate = function( $data ){
		if( $data && $data.state ){
			var $fn = get( iE.fn, $data.state[ 'fn' ], iE.fn.trigger );
			$fn.apply( $( '#' + $data.state[ 'root' ] ) );
		}
	};
}
/**
 *	@module fn
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service fn_invoke
 *	@params key, query
 *	@result 
**/
iE.fn.invoke = function( $in, $data, $request, $status ){
	$in[ 'data' ] = $data;
	var $valid = $in[ 'valid' ] == undefined ? true : $in[ 'valid' ];
	var $cache = get( $in, 'cache-key', false );

	var $workflow = get( $in, 'workflow', false );
	if( $workflow ){
		$in[ 'idef' ] = $workflow;
		$in = iE.rt.execute( $in );
		if( !$in[ 'result' ][ 'valid' ] ){
			die( JSON.stringify( $in ) );
			return false;
		}
	}
	else {
		var $res = iE.fn.trigger.apply( { 'data-trigger': $( $in[ 'root' ] ).attr( 'data-workflow' ), 'in': $in } );
		if( !$res ){
			die( JSON.stringify( $in ) );
			return false;
		}
	}
	
	if( $valid && $cache ){
		iE.state[ $cache ] = $data;
	}
	
	return true;
}
/**
 *	@module fn
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service fn_trigger
 *	@params key, query
 *	@result 
**/
iE.fn.trigger = function( event ){
	var $data = $( this ).attr( 'data-trigger' );

	try {
		if( $( this ).attr( 'in' ) ){
			var $in = $( this ).attr( 'in' );
		}
		else {
			var $in = { root: $( this ) };
			iE.fn.history( this, 'trigger' );
		}
		
		if( $data ){
			$data = $data.split( '://' );

			switch( $data[ 0 ] ){
				case 'rt':
					$in[ 'idef' ] = $data[ 1 ];
					$in = iE.rt.execute( $in );
					return $in[ 'result' ][ 'valid' ];

				case 'mp':
					$in[ 'idef' ] = get( iE.config.mappings, $data[ 1 ], false, '@fn.trigger' );
					$in = iE.rt.execute( $in );
					return $in[ 'result' ][ 'valid' ];

				case 'js':
					$in[ 'idef' ] = JSON.parse( $data[ 1 ] );
					$in = iE.rt.execute( $in );
					return $in[ 'result' ][ 'valid' ];
					
				case 'fn':
				default:
					$data = $data[ 1 ].split( ' ' );
					for( var $i in $data ){
						var $fx = get( iE.fn, $data[ $i ], false, '@fn.trigger No fn:' + $data[ $i ] );
						if( $fx && !$fx.apply( this ) ){
							return false;
						}
					}
					return true;
			}
		}
	}
	catch( $id ){
		return fail( 'Exception : ' + $id + ' @fn.trigger' );
	}

	return false;
}
/**
 *	@module fn
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service fn_error
 *	@params key, query
 *	@result 
**/
iE.fn.error = function( $root, $sel ){
	$root.next( $sel ).stop( true, true ).slideDown( 1000 ).delay( 5000 ).fadeOut( 1000 );
	$root.focus();
	
	return true;
}

/**
 *	@service fn_email
 *	@params key, query
 *	@result 
**/
iE.fn.email = function( $index, $el ){
	var $emailRegex = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;

	if( !$emailRegex.test( $(this).val() ) ){
		iE.fn.error( $( this ), get( iE.config, 'errorsel', '.error' ) );
		return false;
	}
	
	return true;
}

/**
 *	@service fn_match
 *	@params key, query
 *	@result 
**/
iE.fn.match = ( function(){
	var $value = false;

	return function( $index, $el ){
		if( $index && $value && ( $( this ).val() != $value ) ){
			iE.fn.error( $( this ), get( iE.config, 'errorsel', '.error' ) );
			return false;
		}
		
		$value = $( this ).val();
	}
} )();

/**
 *	@service fn_required
 *	@params key, query
 *	@result 
**/
iE.fn.required = function( $index, $el ){
	if( $( this ).val() == '' ){
		iE.fn.error( $( this ), get( iE.config, 'errorsel', '.error' ) );
		return false;
	}
}

/**
 *	@module si
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service si_load
 *	@params 
 *	@result 
**/
iE.si.load = function( $in ){
	var $sel = get( $in, 'sel', false );

	if( $sel ){
		if( get( $in, 'cf', false ) ){
			$in[ 'input' ] = { 'cfmsg': 'data' }

			$msg = iE.ui.confirm( $in );
			if( !$msg[ 'valid' ] ) return $msg;	

			delete $in[ 'input' ];		
		}

		$in[ 'idef' ] = [{
			service: iE.ui.validate,
			sel: $sel + ' .validate'
		},{
			service: iE.ui.attribute,
			el: $sel + ' .lcntr',
			attr: 'disabled',
			val: 'disabled'
		},{
			service: iE.ui.content,
			el: $sel + ' .lcntr',
			output: { 'olddata': 'olddata' }
		},{
			service: iE.io.dom,
			root: $sel,
			input: { 'dtype': 'type', 'dsel': 'sel' },
			output: { 'data':'data', 'url':'url', 'request':'request', 'mime':'mime' }
		},{
			service: iE.si.pool,
			fn: 'unload',
			args: [ 'sel', 'pnl', 'root', 'olddata', 'act', 'anm','dur', 'url', 'data', 'request', 'type', 'process', 'mime', 'sync' ]
		}];

		$in = iE.rt.execute( $in );
		return $in[ 'result' ];
	}
	else {
		fail( 'Selector Not Specified', '@si.load' );
	}

	return success( {}, 'Valid Pool SI' );
}

/**
 *	@module si
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service si_pool
 *	@params 
 *	@result 
**/
iE.si.pool = function( $in ){
	var $key = 'pool://url/' + get( $in, 'url', 'STD_URL' ) + '/data/' + get( $in, 'data', '' );
	var $data = get( iE.state, $key, false );

	if( !get( $in, 'force', false ) && $data ){
		iE.fn[ get( $in, 'fn', 'invoke' ) ]( $in, $data, {}, 'success' );
	}
	else {
		$in[ 'cache-key' ] = $key;
		iE.io[ get( $in, 'io', 'ajax' ) ]( $in );
	}

	if( !get( $in, 'nostop', false ) ){
		return fail( 'Event Propagation Stopped for Pool' );
	}

	return success( {}, 'Valid Pool SI' );
}

/**
 *	@module si
 *	@author Vibhaj Rajan <vibhaj8@gmail.com>
 *
**/

/**
 *	@service si_unload
 *	@params 
 *	@result 
**/
iE.fn.unload = function( $in, $data, $request, $status ){
	var $sel = get( $in, 'sel', false );
	var $workflow = [];
	var $valid = $in[ 'valid' ] == undefined ? true : $in[ 'valid' ];

	if( $in[ 'valid' ] ){
		switch( get( $in, 'chng', false ) ){
			case 'reset' :
				$workflow = [{
					service : iE.ui.trigger,
					el : $sel + ' input[type=reset]',
					event : 'click',
				}];
				break;
			
			case 'hide' :
				$workflow = [{
					service : iE.ui.content,
					el : $sel,
					act : 'none',
					anm : 'fadeout',
					dur : 150
				}];
				break;
				
			case 'hdsl' :
				$workflow = [{
					service : iE.ui.trigger,
					el : $sel + ' input[type=reset]',
					event : 'click',
				},{
					service : iE.ui.content,
					el : $sel,
					act : 'none',
					anm : 'fadeout',
					dur : 150
				}];
				break;
			
			case 'none' :
			default : 
				break;
		}
	}

	$in[ 'idef' ] = $workflow.concat( [{
		service: iE.ui.attribute,
		el: $sel + ' .lcntr',
		attr: 'disabled',
	},{
		service: iE.ui.attribute,
		el: $sel + ' .lcntr',
		attr: 'disabled',
		role: false
	},{
		service: iE.ui.content,
		el: $sel + ' .lcntr',
		input: { 'olddata': 'data' }
	}] );

	iE.rt.execute( $in );
	
	$in[ 'valid' ] = $valid;
	if( !$in[ 'root' ].attr( 'data-workflow' ) ){
		$in[ 'root' ].attr( 'data-workflow', 'rt://html/~/input/{data:data,pnl:el,act:act,anm:anm,dur:dur}/-/html/~/input/{data:data,pnl:el}/role/false/' );
	}
	
	return iE.fn.invoke( $in, $data, $request, $status );
}

