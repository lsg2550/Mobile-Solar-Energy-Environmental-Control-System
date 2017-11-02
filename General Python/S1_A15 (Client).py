#import
import socket

#Init
servername = "127.0.0.1"
port = 43525
clientsock = socket.socket()

#Set up Server
try:
	clientsock.connect((servername, port))
	
finally:
	clientsock.close()



