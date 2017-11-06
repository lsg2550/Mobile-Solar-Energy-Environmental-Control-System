#import
import socket

#Init
servername = "127.0.0.1"
port = 43525
clientsock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
isConnected = False

#Connect to Server
try:
	clientsock.connect((servername, port)) #Connect

	#Receive Server's Password Request
	received = str(clientsock.recv(1024), 'utf-8')
	print("{}".format(received))

	request = input()
	clientsock.sendall(bytes(request + "\n", 'utf-8'))
	received = str(clientsock.recv(1024), 'utf-8')
	print("{}".format(received))
	if received == "GOODINPUT":
		isConnected = True

	while isConnected == True:
		request = input("Would you like to request data? (Y/Quit): ").upper()
		clientsock.sendall(bytes(request + "\n", 'utf-8'))
		received = str(clientsock.recv(1024), 'utf-8')
		if request == "Y":		
			print("Received: {}".format(received))
		elif request == "QUIT" or received == "QUIT":
			break
		else:
			print("Bad Input!")
			break
finally:
	print("Closing Connection...")
	clientsock.close()