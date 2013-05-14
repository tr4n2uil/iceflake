import json
import os

# fiber.Journal
class Journal:

	# constructor
	def __init__( self, path, chunk = 2, prelen = 0 ):
		self.path = path

		# read conf
		try:
			with file( path + '/db.conf' ) as f:
				s = f.read()
		
		# write new conf
		except IOError:
			os.makedirs( self.path + '/data' )
			os.makedirs( self.path + '/counters' )
			with file( path + '/db.conf', 'w' ) as f:
				s = '{"chunk":' + chunk + ',"prelen":' + prelen + '}'
				f.write( s )

		# set conf
		self.conf = json.loads( s )
	
	# get data for key
	def get( self, key ):
		try:
			with file( self.path + self.expand( key ), 'r' ) as f:
				s = f.read()

		except IOError:
			return None

		return s

	# set data for key
	def set( self, data, key = False, edit = False ):
		# auto id
		if not key:
			key = self.auto_id()

		# check path
		p = self.path + self.expand( key )
		d = os.path.dirname( p )

		# create path
		if not os.path.exists( d ):
			os.makedirs( d )

		# prevent override
		if not edit and os.path.isfile( p ):
			return False

		# put data
		with file( p, 'w' ) as f:
			f.write( data )

		# count housekeeping
		if not edit:
			# total count
			p = self.path + '/counters/total'
			if not os.path.isfile( p ):
				with file( p, 'w' ): pass

			with file( p, 'r+' ) as f:
				s = f.read()
				if not s: s = '0'
				s = float( s )
				s += 1
				f.seek( 0, 0 )
				f.write( '%f' % s )

			# level 1 count
			p = self.path + '/counters/' + key[ :self.conf[ 'chunk' ] ]
			if not os.path.isfile( p ):
				with file( p, 'w' ): pass

			with file( p, 'r+' ) as f:
				s = f.read()
				if not s: s = '0'
				s = float( s )
				s += 1
				f.seek( 0, 0 )
				f.write( '%f' % s )

		return True

	# check key exists
	def exists( self, key ):
		return os.path.isfile( self.path + self.expand( key ) )

	# get data in sorted order with pagination
	def all( self, key = False, keys = False, offset = 0, limit = None ):
		p = self.path
		if key:
			p += self.expand( key )
			p = os.path.dirname( p )
		else:
			p += '/data'

		within = False if key else True
		l = len( key ) if key else 0
		resk = [] 
		resd = {}
		i = 0
		limit = offset + limit if limit else None
		for root, dirnames, filenames in os.walk( p ):
			dirnames.sort()
			filenames.sort()

			for fl in filenames:
				k = fl[ 5: ]
				if key:
					if not within:
						if k[ :l ] == key:
							within = True
					else:
						if k[ :l ] != key:
							within = False
							break;

				if within:
					if i >= offset and ( limit == None or i < limit ):
						resk.append( k )
						if not keys:
							with file( os.path.join( root, fl ) ) as f:
								s = f.read()
							resd[ k ] = s
					i += 1

		return resk, resd

	# get all counters
	def counters( self ):
		cnt = {}
		for e in os.listdir( self.path + '/counters' ):
			p = self.path + '/counters/' + e
			with file( p ) as f:
				s = f.read()
			cnt[ e ] = float( s )

		return cnt

	# expand key to path
	def expand( self, key ):
		l = [ key[ x:x + self.conf[ 'chunk' ] ] for x in range( self.conf[ 'prelen' ], len( key ), 2 ) ]
		l.pop()
		l.append( 'data.' + key )
		return '/data/' + '/'.join( l )

	# auto generate id
	def auto_id( self, id = None ):
		p = self.path + '/counters/auto.id'
		if not os.path.isfile( p ):
			with file( p, 'w' ): pass

		with file( p, 'r+' ) as f:
			s = f.read()
			if not s: s = '0'
			s = id if id else float( s )
			s += 1
			f.seek( 0, 0 )
			f.write( '%f' % s )

		return s
