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

#--------------------------------------------------------------------------------------------------------------#

#Socket Connection Init
servername = '192.168.2.29'
port = 8080
clientSocket = socket.socket()
credentialsVerified = False

#Client/Server Communication
try:
    clientSocket.connect((servername, port)) #Connect
	
    while True:
        #Sign In - signin\username\password
        clientSocket.sendall(bytes('signin\n' + 'rp1\n' + 'pie!\n', 'utf-8'))
        clientReceived = str(clientSocket.recv(1024), 'utf-8').upper()

        if clientReceived == 'ACCEPT':
            print('{}'.format('Login Successful!'))

            timeUpdate = time.time()
            timeRequest = timeUpdate
            while True:
                timeCurrent = time.time()

                if float(timeCurrent - timeUpdate) > 30:
                    timeUpdate = timeCurrent #Update timeUpdate
                    clientSocket.sendall(bytes('xml' + '\n', 'utf-8'))
                    print('Preparing to send XML to Server')
                    xmlFile = open(filePath, 'rb')
                    xmlBytes = xmlFile.read(1024)
                    while(xmlBytes):
                        clientSocket.send(xmlBytes)
                        xmlBytes = xmlFile.read(1024)
                    xmlFile.close()
                    print('Done Sending')
                
                if float(timeCurrent - timeRequest) > 60:
                    timeRequest = timeCurrent #Update timeRequest
                    clientSocket.sendall(bytes('request' + '\n', 'utf-8'))
                    print('Preparing to receive vital information from Server')
                    while True:
                        clientReceived = str(clientSocket.recv(1024), 'utf-8')
                        print('{}'.format(clientReceived))
                        if clientReceived == 'LOGEND':
                            print('Vital Information Received')
                            break
        #This will be reached if main code breaks or if login is incorrect
        break
except Exception as e:
    print('Exception Occurred' + str(e))
finally:
    print('Closing Connection...')
    clientSocket.close()