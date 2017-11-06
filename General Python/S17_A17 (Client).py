#import
import socket

#Init
servername = "127.0.0.1"
port = 43525
clientsock = socket.socket()
isConnected = False

#Connect to Server
try:
	clientsock.connect((servername, port)) #Connect

	#Receive Server's Password Request
	received = str(clientsock.recv(1024), 'utf-8')
	print("{}".format(received))

	while True:
		request = input()
		clientsock.sendall(bytes(request + "\n", 'utf-8'))
		received = str(clientsock.recv(1024), 'utf-8')
		if received == "Bad Password! Closing Session...":
			print(received)
			break
		else:
			isConnected = True
			break

	while isConnected == True:
		request = input("Would you like to request data? (Y/Quit): ").upper()
		clientsock.sendall(bytes(request + "\n", 'utf-8'))
		if request == "Y":		
			received = str(clientsock.recv(1024), 'utf-8')
			print("Received: {}".format(received))
		elif request == "QUIT":
			break		
		else:
			print("Wrong Input!")
			
finally:
	print("Closing Connection...")
	clientsock.close()



