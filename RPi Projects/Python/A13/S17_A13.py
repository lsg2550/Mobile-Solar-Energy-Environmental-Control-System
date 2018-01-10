#Import
import socket

#Vars
hostname = "google.com"
port = 80

#Connection
try:
    sock = socket.socket()
    sock.connect((hostname, port))
except Exception as e:
    print(e)
    quit()

#Output
print("Remote IP Address: " + str(sock.getpeername()[0]))
print("Remote Port Address: " + str(sock.getpeername()[1]))
print("Local IP Address: " + str(sock.getsockname()[0]))
print("Local Port Address: " + str(sock.getsockname()[1]))

#Notes
#socket.getpeername() returns an array with remote socket information:
#[0] = Remote IP Address
#[1] = Remote Port Address
#socket.getsockname() returns an array with local socket information:
#[0] = Local IP Address
#[1] = Local Port Address
