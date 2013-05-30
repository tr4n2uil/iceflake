import datetime
import socket
import threading
import SocketServer

#import rt

class ThreadedTCPRequestHandler( SocketServer.StreamRequestHandler ):

	def handle( self ):
		request = ''

		while 1:
			data = self.rfile.readline()
			if not data or data[ 0 ] == '\r':
				break
			request += data

		cur_thread = threading.current_thread()
		
		print '[', datetime.datetime.now(), '] [', self.client_address[ 0 ], '] [', cur_thread.name, '] ', request
		response = 'HTTP/1.0 200 OK\nDate: Thu, 30 May 2013 04:58:14 GMT\nServer: Apache/2.2.22 (Unix) DAV/2 PHP/5.3.15 with Suhosin-Patch\nX-Powered-By: PHP/5.3.15\nConnection: close\nContent-Type: text/html\n\n\nDATA\nSuccess\n' #proxy.process( request )

		self.request.sendall( response )

class ThreadedTCPServer( SocketServer.ThreadingMixIn, SocketServer.TCPServer ):
    pass

class Server:

	# constructor
	def __init__( self, host = '127.0.0.1', port = 8000 ):
		self.addr = ( host, port )
		self.server = ThreadedTCPServer( self.addr, ThreadedTCPRequestHandler )

	def serve( self ):
		print '[', datetime.datetime.now(), '] [', self.addr, '] IO Server Started'
		self.server.serve_forever()

