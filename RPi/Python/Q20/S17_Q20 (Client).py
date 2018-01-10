#import
import socket

#Init
servername = "127.0.0.1"
port = 43523
clientsock = socket.socket()

try:
	#Connect
	clientsock.connect((servername, port))
	
	while True:
		#End Program or Continue Requesting Data
		request = input("Please enter 'Quit' or 'name of person': ")
		clientsock.sendall(bytes(request + "\n", 'utf-8'))
		
		#Print Received Data
		received = str(clientsock.recv(1024), 'utf-8')
		print("{}".format(received))
		
		#Process Received Data
		if received == "QUIT":
			break
finally: #Finally, just close connection
	print("Closing Connection...")
	clientsock.close()