import datetime
import socket
import thread

#import rt


# io.server.Server
class Server:

	# constructor
	def __init__( self, conf, host = '127.0.0.1', port = 8000 ):
		self.proxy = 1#rt.Proxy( conf )
		
		sock = socket.socket( socket.AF_INET, socket.SOCK_STREAM )
		sock.setsockopt( socket.SOL_SOCKET, socket.SO_REUSEADDR, 1 )
		sock.bind( ( host, port ) )
		sock.listen( 5 )

		self.socket = sock
		self. addr = ( host, port )


	# start server
	def start( self ):
		print '[', datetime.datetime.now(), '] [', self.addr, '] IO Server Started'
		while 1:
			csock, addr = self.socket.accept()
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
	print '[', datetime.datetime.now(), '] [', address, '] ', request

	response = 'HTTP/1.0 200 OK\nDate: Thu, 30 May 2013 04:58:14 GMT\nServer: Apache/2.2.22 (Unix) DAV/2 PHP/5.3.15 with Suhosin-Patch\nX-Powered-By: PHP/5.3.15\nConnection: close\nContent-Type: text/html\n\n\nDATA\nSuccess\n' #proxy.process( request )
	csock.send( response )
	print 'sent'
	csock.shutdown( 2 )
	print 'shutdown'
	csock.close()
	print 'closed'



