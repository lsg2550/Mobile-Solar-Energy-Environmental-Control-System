#########################################################################################
# gensend_xml.py (Generate and Send XML)                                                #
# Generates the XML by reading from sensors and storing the variables in an XML file,   #
# then sends the XML file to the website using an FTP connection                        #
#########################################################################################

#import
import os
import time
from xml.etree import ElementTree
from connect_to_ftp import sendXML
from datetime import datetime
from pytz import timezone

#Init
tempXML = "<currentstatus><log></log><temperature>20</temperature><battery>12.6</battery><solarpanel>not charging</solarpanel><exhaust>off</exhaust><photo>0.5</photo><rpid></rpid></currentstatus>"
startTime = time.time()
dateFormat = "%Y-%m-%d %H:%M:%S"

while True:
    #Read from sensors and put data into XML
    

    #Timestamps
    currentTimeStampForLog = datetime.now(timezone("UTC")).strftime(dateFormat)
    currentTimeStampForFileName = currentTimeStampForLog.replace(":", "-")

    #Create File
    fileName = "status(" + currentTimeStampForFileName + ").xml"
    fileXML = open(fileName, "w+")
    fileXML.write(tempXML) #TODO: Replace this with looped code to fill XML with data from sensors
    fileXML.close()

    #Parse XML & Update XML's log to current timestamp
    fileXML = open(fileName, "r+")
    xmlParsed = ElementTree.parse(fileXML)
    fileXML.close()
    fileXML = open(fileName, "w").close()

    xmlRoot = xmlParsed.getroot()
    xmlLogElement = xmlRoot.find("log")
    xmlLogElement.text = str(currentTimeStampForLog)
    xmlRpidElement = xmlRoot.find("rpid")
    xmlRpidElement.text = str(0) #This will be the raspberry pi's identification number

    fileXML = open(fileName, "r+")
    xmlParsed.write(fileXML)
    fileXML.close()

    #Send XML to Server
    try:
        #Check 'temporary folder' for any unsent XML files, then send them and delete them, then continue on sending the most recent file (update)
        sendXML(fileName)
    except:
        #Code to store XML into a 'temporary folder', and continue on with the program
        print("Could not connect to server...")
        print("Storing XML into temporary folder...")
    finally:
        #Wait for 60 seconds
        timer = (time.time() - startTime) % 60
        print("Standby for (60.0 - {}) = {} seconds".format(str(timer), str((60.0 - timer))))
        time.sleep(60.0 - timer)
#while end