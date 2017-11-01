#Imports
import socket

#Code
hostname = "google.com"
port = 80
sock = socket.socket()

#Remote
print("Remote IP Address:" + socket.gethostbyname(hostname))
print("Remote Port Address:")

#Local
print("Local IP Address:")
print("Local Port Address:")


#To see if port is open
result = sock.connect((hostname, port))
