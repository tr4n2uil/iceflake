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

