#import
import os
import time
import socket
from datetime import datetime
from xml.etree import ElementTree

#------------------------------TODO: Read from sensors and put data into XML-----------------------------------#

#Get File
fileName = 'status.xml'
filePath = os.path.abspath(fileName)

#Parse XML
xmlParsed = ElementTree.parse(filePath)
xmlRoot = xmlParsed.getroot()

#Update XML's log to current timestamp
xmlLogElement = xmlRoot.find('log')
xmlLogElement.text = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
xmlParsed.write('status.xml')

'''
#Create String to Send to Server
xml = []
for line in xmlFile:
	xml.append(line)
xmlString = ''.join(xml)
print(xmlString)
'''

#--------------------------------------------------------------------------------------------------------------#

#Socket Connection Init
servername = '192.168.2.29'
port = 8080
client = socket.socket()
credentialsVerified = False;

#Client/Server Communication
try:
	client.connect((servername, port)) #Connect
	client.sendall(bytes(,'utf-8'))
	
	while True:
            client.sendall(bytes('signin\n' + '\n' + '\n'))
            if str(client.recv(1024), 'utf-8').upper() == 'ACCEPT':
                print('{}'.format('Login Successful!'))
                credentialsVerified = True;
            if credentialsVerified:
                #End Program or Continue Requesting Data
		request = input('What would you like to request from the server? - 'Quit' closes the connection.\n')
		client.sendall(bytes(request + '\n', 'utf-8'))
		
		#Process Received Data
		if request.upper() == 'QUIT':
			break
		elif request.upper() == 'XML':
			print('Preparing to send XML to Server')
			xmlFile = open(filePath, 'rb')
			xmlBytes = xmlFile.read(1024)
			time.sleep(5)
			while(xmlBytes):
				client.send(xmlBytes)
				xmlBytes = xmlFile.read(1024)
			xmlFile.close()
			print('Done Sending')
		
		#Print Received Data
		received = str(client.recv(1024), 'utf-8')
		print('{}'.format(received))
finally:
	print('Closing Connection...')
	client.close()
