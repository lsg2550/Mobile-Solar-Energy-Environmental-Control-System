#########################################################################################
# gensend_xml.py (Generate and Send XML)                                                #
# Generates the XML by reading from sensors and storing the variables in an XML file,   #
# then sends the XML file to the website using an FTP connection                        #
#########################################################################################

#import
import os
import time
from xml.etree import ElementTree
from connecttoftp import sendXML
from datetime import datetime

tempXML = "<currentstatus><log></log><temperature>20</temperature><battery>12.6</battery><solarpanel>not charging</solarpanel><exhaust>off</exhaust><photo>0.5</photo><rpid></rpid></currentstatus>"

#------------------------------TODO: Read from sensors and put data into XML-----------------------------------#
#Timestamps
currentTimeStampForLog = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
currentTimeStampForFileName = currentTimeStampForLog.replace(":", "-")

#Create File
fileName = "status(" + currentTimeStampForFileName + ").xml"
fileXML = open(fileName, "w+")
fileXML.write(tempXML)
fileXML.close()

#Parse XML & Update XML's log to current timestamp
fileXML = open(fileName, "r+")
xmlParsed = ElementTree.parse(fileXML)
xmlRoot = xmlParsed.getroot()
xmlLogElement = xmlRoot.find("log")
xmlLogElement.text = currentTimeStampForLog
xmlRpidElement = xmlRoot.find("rpid")
xmlRpidElement.text = 0 #This will be the raspberry pi's identification number
xmlParsed.write(fileXML)
fileXML.close()

#Send XML to Server
sendXML(fileName)