#import
import socket

#Init
servername = "127.0.0.1"
port = 43525 #Random Port
clientsock = socket.socket()

#Connect to Server
try:
	clientsock.connect((servername, port))
	while True:
		request = input("Would you like to request data? (Y/Quit): ").upper()
		if request == "Y":
			data = input("Data Available: 0-9: ")				
			clientsock.sendall(bytes(data + "\n", "utf-8"))
			received = str(clientsock.recv(1024), "utf-8")
			print("Received: {}".format(received))
		elif request == "QUIT":
			clientsock.sendall(bytes(request + "\n", "utf-8"))
			break
		else:
			print("Wrong Input!")
finally:
	print("Closing Connection...")
	clientsock.close()



