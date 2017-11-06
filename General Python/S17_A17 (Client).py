#import
import socket

#Init
servername = "127.0.0.1"
port = 43525
clientsock = socket.socket()
isConnected = False

try:
	#Connect
	clientsock.connect((servername, port))

	#Receive Server's Password Request
	received = str(clientsock.recv(1024), 'utf-8')
	print("{}".format(received))

	#Give Server Password
	request = input()
	clientsock.sendall(bytes(request + "\n", 'utf-8'))
	received = str(clientsock.recv(1024), 'utf-8')
	
	#Get Server's Response for Password
	print("{}".format(received))
	if received == "GOODINPUT":
		isConnected = True

	#Loop for further data extraction
	while isConnected == True:
		#Give Client Choice to End Program or Continue Requesting Data
		request = input("Would you like to request data? (Y/Quit): ").upper()
		clientsock.sendall(bytes(request + "\n", 'utf-8'))
		
		#Print Received Data
		received = str(clientsock.recv(1024), 'utf-8')
		print("Received: {}".format(received))
		
		#Process Received Data
		if received == "QUIT" or received == "NOMORE" or received == "BADINPUT":
			break
finally: #Finally, just close connection
	print("Closing Connection...")
	clientsock.close()