#import
import socket
import socketserver

class RequestHandlerClass(socketserver.BaseRequestHandler):
	def handle(self):
		self.data = self.request.recv(1024).strip()
		print("{} wrote:".format(self.client_address[0]))
		print(self.data)
		self.request.sendall(self.data.upper())

#Init
servername = "127.0.0.1"
port = 43525

#Set up Server
server = socketserver.BaseServer((servername, port), RequestHandlerClass)
server.serve_forever()



