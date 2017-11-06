#import
import socket

#Init
servername = "127.0.0.1"
port = 43525 #Random Port
clientsock = socket.socket()
data = "Hello, Server"

#Connect to Server
try:
	clientsock.connect((servername, port))
	clientsock.sendall(bytes(data + "\n", "utf-8"))
	received = str(clientsock.recv(1024), "utf-8")
finally:
	clientsock.close()

print("Sent: {}".format(data))
print("Received: {}".format(received))



