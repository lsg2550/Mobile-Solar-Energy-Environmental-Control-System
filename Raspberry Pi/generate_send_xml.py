#########################################################################################
# gensend_xml.py (Generate and Send XML)                                                #
# Generates the XML by reading from sensors and storing the variables in an XML file,   #
# then sends the XML file to the website using an FTP connection                        #
#########################################################################################

#import
import os
import time
import connect_to_ftp as CTF
import read_analog_from_adc as RAFA
from threading import Thread
from xml.etree import ElementTree
from datetime import datetime
from pytz import timezone

#Create Storage Directories
xmlStorageDirectory = "XMLTempStorage/"
xmlSentDirectory = "XMLSentStorage/"
if not os.path.isdir(xmlStorageDirectory): os.mkdir(xmlStorageDirectory)
if not os.path.isdir(xmlSentDirectory): os.mkdir(xmlSentDirectory)

#Date & Time Format for XML
dateAndTimeFormat = "%Y-%m-%d %H:%M:%S"

#TemporaryXML Format
tempXML = '''
<currentstatus>
    <log></log>
    <temperature>20</temperature>
    <battery></battery>
    <solarpanel></solarpanel>
    <solarpanelvalue></solarpanelvalue>
    <exhaust>off</exhaust>
    <photo>0.5</photo>
    <rpid></rpid>
</currentstatus>
'''

def GetAndSendXML(fileName): #Send XML to Server
    try:
        #Check xmlStorageDirectory for any unsent XML files
        files = os.listdir(xmlStorageDirectory)
        
        #Send unsent XML, if any
        for storedFile in files:
            tempFile = str(storedFile)
            if tempFile.endswith(".xml"):
                CTF.SendXML(xmlStorageDirectory + tempFile)
                os.rename(xmlStorageDirectory + tempFile, xmlSentDirectory + tempFile)
                
        #Send recent XML
        CTF.SendXML(fileName)
        os.rename(fileName, xmlSentDirectory + fileName)
    except Exception as e:
        #print(e)
        os.rename(fileName, xmlStorageDirectory + fileName) #Move File to Temporary Storage Folder
        print("Could not connect to server...\nStoring XML into {}...".format(xmlStorageDirectory))
    
    #Debug Output
    print("Background thread done!")

def Main():
    #Program Start Time
    startTime = time.time()
    
    while True:
        #Generate Timestamps
        timeStampForLog = datetime.now(timezone("UTC")).strftime(dateAndTimeFormat)
        timeStampForFileName = timeStampForLog.replace(":", "-")
        
        #Create File & Create XML Element Tree Object
        fileName = "status(" + timeStampForFileName + ").xml"
        fileXML = open(fileName, "w+")
        xmlParsed = ElementTree.ElementTree(ElementTree.fromstring(tempXML))
        xmlRoot = xmlParsed.getroot()

        #Read from Sensors
        sensorDictionary = RAFA.ReadFromSensors()
        xmlLogElement = xmlRoot.find("log")
        xmlRpidElement = xmlRoot.find("rpid")
        xmlLogElement.text = str(timeStampForLog)
        xmlRpidElement.text = str(0) #This will be the raspberry pi's identification number - since we will only be using 1 RPi, we can hardcode 0
        for key, value in sensorDictionary.items():
            xmlElement = xmlRoot.find(key)
            xmlElement.text = str(value)
        
        #Write and Close File
        xmlParsed.write(fileName)
        fileXML.close()

        #Send XML and wait for 60 seconds for the next interval
        sendXMLThread = Thread(target=GetAndSendXML, args=(fileName,))
        sendXMLThread.start()
        timer = (time.time() - startTime) % 60
        print("XML transfer moved to a background thread...\nMain thread is now on standby for {0:.2} seconds...".format(str((60.0 - timer))))
        time.sleep(60.0 - timer)
    #while end

if __name__ == "__main__":
    print("Program Start")
    Main()