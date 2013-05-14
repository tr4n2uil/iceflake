import os
import urllib

# runtime.Proxy
class Proxy:

	# constructor
	def __init__( self, conf ):
		# read conf
		with file( conf ) as f:
			s = f.read()
			self.conf = json.loads( s )


	# success helper
	def success( self, data = {}, msg = 'Executed Successfully', status = 200, details = '' ):
		if 'valid' not in data:
			data[ 'valid' ] = True
		data[ 'msg' ] = msg
		data[ 'status' ] = status
		data[ 'details' ] = details
		return data

	# fail helper
	def fail( self, msg, status = 500, details = 'Exception Occured' ):
		return { 'valid': False, 'msg': msg, 'status': status, 'details': details }

	# get helper
	def get( self, dct, key, die = '' ):
		if key not in dct and die:
			return self.fail( 'Key Not Found', 502, 'Key Not Found: ' + key + ' @' + die )
		return dct.get[ key ]

	# set helper
	def set( self, dct, key, val, die = '' ):
		if key in dct:
			if die:
				return fail( 'Key Already Exists', 503, 'Key Already Exists: ' + key + ' @' + die )
			else
				return dct
		dct[ key ] = val
		return dct


	# process request
	def process( self, request ):
		invoke = self.get( request, 'invoke', 'Proxy.process/get_invoke' )

		if type( invoke ) is str:
			response = self.navigator( request )
		elif type( invoke ) is dict:
			response = self.message( request )
		elif type( invoke ) is list:
			response = self.workflow( request )

		return response.get( 'invoke' ) if response.get( 'valid', False ) else response


	# process message
	def message( self, request ):
		invoke = self.get( request, 'invoke', 'Proxy.message/get_invoke' )
		query = self.get( invoke, 'service', 'Proxy.message/get_service_query' )

		# read mapping
		mapping = self.get( self.conf, query, 'Proxy.message/lookup_service__in_conf' )
		service = mapping[ 'service' ]

		# args
		for key in invoke.get( 'args', [] ):
			invoke = self.set( invoke, key, request.get( key, None ) )

		# set
		for key, value in mapping[ 'set' ].items():
			if key not in invoke:
				invoke = self.set( invoke, value, invoke.get( key ) )

		# conf
		for key, value in mapping[ 'conf' ].items():
			invoke = self.set( invoke, key, value )

		# input
		for key, value in invoke.get( 'input', {} ):
			invoke = self.set( invoke, value, request.get( key, None ) )

		try:
			# load module
			parts = service.split('.')
			__import__( parts[ 0 ] )

			# run
			request[ 'invoke' ] = invoke = eval( service )( **invoke )

			# input
			for key, value in invoke.get( 'output', {} ):
				request = self.set( request, value, invoke.get( key, None ) )

			# return success
			return self.success( request, 'Valid Message Execution' )

		except Exception as e:
			return self.fail( 'Exception Occured', 504, e.args )


	# process workflow
	def workflow( self, request ):
		invoke = self.get( request, 'invoke', 'Proxy.workflow/get_invoke' )

		if 'valid' not in invoke:
			invoke[ 'valid' ] = True

		state = invoke[ 'valid' ]
		hops = {}
		hop = 0
		l = len( invoke )

		while( hop < l ):
			msg = invoke[ hop ]

			if not hops and ( self.get( msg, 'role', True ) != state ):
				hop += 1
				continue

			request[ 'invoke' ] = msg
			request = self.message( request )

			state = request[ 'valid' ]
			hops = self.get( msg, 'hops', {} )
			hop = self.get( hops, state, hop + 1 )

			delete request[ 'invoke' ]

		if not request[ 'valid' ]:
			return request

		return self.success( request, 'Valid Workflow Execution' )


	# process navigator
	def navigator( self, request ):
		invoke = self.get( request, 'invoke', 'Proxy.navigator/get_invoke' )

		req = invoke.split( '/-/' )
		total = 0

		for j, string in req.items():
			total += 1
			invoke = {}

			args = string.aplit( '~' )

			path = args[ 0 ].split( '/' )
			invoke[ 'service' ] = path[ 0 ]
			max = len( path )

			if path[ max - 1 ] == '':
				delete path[ max - 1 ]

			for i in range( 1, max ):
				request[ i - 1 ] = path[ i ]

			try:
				path = args[ 1 ].split( '/' )
				max = len( path )

				for i in range( 0, max, 2 ):
					invoke[ path[ i ] ] = self.parse( path[ i + 1 ] )

			except IndexError:
				pass

			req[ j ] = invoke

		request[ 'invoke' ] = req[ 0 ] if total == 1 else req
		return self.process( request )


	# parse navigator arg
	def parse( self, arg = '' ):
		if arg:
			if arg[ 0 ] == '[':
				arg = arg[ 1:-1 ]
				return arg.aplit( ',' )
			
			elif arg[ 0 ] = '{':
				arg = arg[ 1:-1 ]
				arg = arg.split( ',' )
				res = {}
				for a in arg:
					if a:
						parts = a.split( '=' )
						res[ parts[ 0 ] ] = parts[ 1 ]
				return res

			elif arg == 'true'
				return True

			elif arg == 'false':
				return False

			else:
				return urllib.unquote( arg )



