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
