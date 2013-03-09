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

