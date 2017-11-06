#import
import socket

#Vars
file = open("fports.txt","r") #File
portdictionary = {} #Array of integer (key):boolean (value)

#Read From File (fports) & Output
hostname = file.readline().rstrip("\n")
print(hostname)

for line in file: #For each line in file; we skipped the hostname line because we read that line previously
    port = int(line.rstrip("\n")) #i.rstrip("\n") because reading newlines adds '\n' to the string
    sock = socket.socket() 
    result = sock.connect_ex((hostname, port))

    if(result == 0):
        print(str(port) + "\t" + "False")
    else:
        print(str(port) + "\t" + "True")

    sock.close()

