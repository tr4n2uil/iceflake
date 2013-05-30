import datetime
import socket
import thread

import rt


# flow.Server
class Server:

	# constructor
	def __init__( self, conf, host = 'localhost', port = '8000' ):
		self.proxy = rt.Proxy( conf )
		
		sock = socket.socket( socket.AF_INET, socket.SOCK_STREAM )
		sock.setsockopt( socket.SOL_SOCKET, socket.SO_REUSEADDR, 1 )
		sock.bind( ( host, port ) )
		sock.listen( 5 )

		self.socket = sock
		self. addr = ( host, port )


	# start server
	def start( self ):
		print '[', datetime.datetime.now(), '] [', self.addr, '] Flow Server Started'
		while 1:
			csock, addr = self.sock.accept()
			thread.start_new_thread( handler, ( csock, addr, self.proxy ) )


# flow.handler
def handler( csock, address, proxy ):
	request = ''
	while 1:
		data = csock.recv( 1024 )
		if not data or data[ 0 ] == '\r':
			break
		request += data

	request = request.strip()
	print '[', datetime.datetime.now(), '] [', addr, '] ', request

	response = proxy.process( request )
	csock.send( response )
	csock.close()