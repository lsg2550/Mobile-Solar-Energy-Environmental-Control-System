'''
gensend_xml.py (Generate and Send XML)

Generates the XML by reading from sensors and storing the variables in an XML file,
then sends the XML file to the website using an FTP connection
'''

#import
import os
import time
import socket
from datetime import datetime
from xml.etree import ElementTree
from ftplib import FTP

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

#------------------------------ Connect to 000webhost and send XML -----------------------------------#

ftp = FTP('files.000webhost.com')
ftp.login('user', 'pass')
xml_file = open("filename", "rb")
ftp.storbinary("STOR public_html/filename", xml_file)
xml_file.close()