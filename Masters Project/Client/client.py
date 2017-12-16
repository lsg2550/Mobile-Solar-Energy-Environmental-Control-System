#import
import socket

#Init
servername = "127.0.0.1"
port = 8080
client = socket.socket()

try:
	client.connect((servername, port)) #Connect
	
	while True:
		#End Program or Continue Requesting Data
		request = input("What would you like to request from the server? - 'Quit' closes the connection.\n")
		client.sendall(bytes(request + "\n", 'utf-8'))
		
		#Process Received Data
		if request.upper() == "QUIT":
			break
		
		#Print Received Data
		received = str(client.recv(1024), 'utf-8')
		print("{}".format(received))
finally: #Finally, just close connection
	print("Closing Connection...")
	client.close()