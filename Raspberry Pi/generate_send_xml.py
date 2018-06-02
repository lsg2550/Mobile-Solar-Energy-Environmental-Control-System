#########################################################################################
# gensend_xml.py (Generate and Send XML)                                                #
# Generates the XML by reading from sensors and storing the variables in an XML file,   #
# then sends the XML file to the website using an FTP connection                        #
#########################################################################################

#import
import os
import time
from xml.etree import ElementTree
from connect_to_ftp import SendXML
from read_analog_from_adc import ReadFromSensors
from datetime import datetime
from pytz import timezone

#Init
xmlStorageDirectory = "xmlstorage"
try:
    os.mkdir(xmlStorageDirectory)
except:
    pass
tempXML = "<currentstatus><log></log><temperature>20</temperature><battery></battery><solarpanel></solarpanel><solarpanelvalue></solarpanelvalue><exhaust>off</exhaust><photo>0.5</photo><rpid></rpid></currentstatus>"
startTime = time.time()
dateFormat = "%Y-%m-%d %H:%M:%S"

while True:
    #Timestamps
    currentTimeStampForLog = datetime.now(timezone("UTC")).strftime(dateFormat)
    currentTimeStampForFileName = currentTimeStampForLog.replace(":", "-")
    
    #Create File
    fileName = "status(" + currentTimeStampForFileName + ").xml"
    fileXML = open(fileName, "w+")
    fileXML.write(tempXML) #TODO: Replace this with looped code to fill XML with data from sensors
    fileXML.close()
    
    #Read from sensors & Parse XML & Update XML's log to current timestamp
    fileXML = open(fileName, "r+")
    xmlParsed = ElementTree.parse(fileXML)
    xmlRoot = xmlParsed.getroot()
    fileXML.close()
    fileXML = open(fileName, "w").close()
    fileXML = open(fileName, "r+")

    sensorDictionary = ReadFromSensors()
    for key, value in sensorDictionary.items():
        xmlElement = xmlRoot.find(key)
        xmlElement.text = str(value)
    #foreach end 
    xmlLogElement = xmlRoot.find("log")
    xmlLogElement.text = str(currentTimeStampForLog)
    xmlRpidElement = xmlRoot.find("rpid")
    xmlRpidElement.text = str(0) #This will be the raspberry pi's identification number
    xmlParsed.write(fileXML)
    fileXML.close()

    #Send XML to Server
    try:
        #Check 'temporary folder' for any unsent XML files, then send them and delete them, then continue on sending the most recent file (update)
        #storedXMLFiles = []
        files = os.listdir(xmlStorageDirectory)
        for storedFile in files:
            tempFile = str(storedFile)
            if tempFile.endswith(".xml"):
                #storedXMLFiles.append(tempFile)
                print(tempFile)
                SendXML(xmlStorageDirectory + "/" + tempFile)
        SendXML(fileName)
    except Exception as e:
        #print(e)
        os.rename(fileName, xmlStorageDirectory + "/" + fileName) #Move File to Temporary Storage Folder
        print("Could not connect to server...")
        print("Storing XML into temporary folder...")
    finally:
        #Wait for 60 seconds
        timer = (time.time() - startTime) % 60
        print("Standby for {0:.2} seconds".format(str((60.0 - timer))))
        time.sleep(60.0 - timer)
#while end