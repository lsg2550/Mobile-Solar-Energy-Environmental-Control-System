#import
import socket

#Init
servername = "127.0.0.1"
port = 8080
client = socket.socket()

try:
	#Connect
	client.connect((servername, port))
	
	while True:
		#End Program or Continue Requesting Data
		request = input("Enter 'Quit': ")
		
		#Process Received Data
		if request.upper() == "QUIT":
			break
finally: #Finally, just close connection
	print("Closing Connection...")
	client.close()