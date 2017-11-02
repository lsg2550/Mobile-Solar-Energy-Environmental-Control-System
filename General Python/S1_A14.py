#import
import socket

#Vars
file = open("fports.txt","r") #File
portdictionary = {} #Array of integer (key):boolean (value)

#Read From File (fports)
hostname = file.readline().rstrip("\n")

for line in file: #For each line in file; we skipped the hostname line because we read that line previously
    port = int(line.rstrip("\n")) #i.rstrip("\n") because reading newlines adds '\n' to the string
    sock = socket.socket() 
    result = sock.connect_ex((hostname, port))
    if(result == 0):
        portdictionary[port] = False
    else:
        portdictionary[port] = True
    sock.close()

#Output
print(hostname)
for key in portdictionary:
    print(str(key) + "\t" + str(portdictionary[key]))
