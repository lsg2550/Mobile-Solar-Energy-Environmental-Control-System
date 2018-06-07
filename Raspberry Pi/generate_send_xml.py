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
    <temperature></temperature>
    <battery></battery>
    <solarpanel></solarpanel>
    <solarpanelvalue></solarpanelvalue>
    <exhaust>off</exhaust>
    <photo>0.5</photo>
    <rpid></rpid>
</currentstatus>
'''

#RaspberryPi ID (rpid) - In an actual environment, there will be a file or some other form of idenfication which this program will read from - however in case I will hard code 0
rpid = 0

def GetAndSendXML(xmlFileName): #Send XML to Server
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
        CTF.SendXML(xmlFileName)
        os.rename(xmlFileName, xmlSentDirectory + xmlFileName)
    except Exception as e:
        #print(e)
        os.rename(xmlFileName, xmlStorageDirectory + xmlFileName) #Move File to Temporary Storage Folder
        print("Could not connect to server...\nStoring XML into {}...".format(xmlStorageDirectory))
    #Debug Output
    print("Background thread done!")

def Main():
    #Program Start Time
    startTime = time.time()
    
    while True:
        #Retrieve XML Files of Thresholds set by Users
        try:
            CTF.RetrieveXML(rpid)
        except:
            pass #There is a default.xml which the pi will initially resort to if it can't connect to the internt on the first try. Otherwise, it will reuse the rpid.xml it already retrieved previously
        thresholdFileName = str(rpid) + ".xml"
        thresholdFile = open(thresholdFileName, "r")
        thresholdParsed = ElementTree.parse(thresholdFile)
        thresholdRoot = thresholdParsed.getroot()
        thresholdVoltageLower = thresholdRoot.find("voltagelower").text
        thresholdVoltageUpper = thresholdRoot.find("voltageupper").text
        thresholdTemperatureLower = thresholdRoot.find("temperaturelower").text
        thresholdTemperatureUpper = thresholdRoot.find("temperatureupper").text
        #print("Voltage Lower Threshold: {}\nVoltage Upper Threshold: {}\nTemperature Lower Threshold: {}\nTemperature Upper Threshold: {}".format(thresholdVoltageLower, thresholdVoltageUpper, thresholdTemperatureLower, thresholdTemperatureUpper))
        
        #Generate Timestamps
        timeStampForLog = datetime.now(timezone("UTC")).strftime(dateAndTimeFormat)
        timeStampForFileName = timeStampForLog.replace(":", "-")
        
        #Create File & Create XML Element Tree Object
        xmlFileName = "status(" + timeStampForFileName + ").xml"
        xmlFile = open(xmlFileName, "w+")
        xmlParsed = ElementTree.ElementTree(ElementTree.fromstring(tempXML))
        xmlRoot = xmlParsed.getroot()

        #Read from Sensors
        sensorDictionary = RAFA.ReadFromSensors(thresholdVoltageLower, thresholdVoltageUpper, thresholdTemperatureLower, thresholdTemperatureUpper)
        xmlLogElement = xmlRoot.find("log")
        xmlRpidElement = xmlRoot.find("rpid")
        xmlLogElement.text = str(timeStampForLog)
        xmlRpidElement.text = str(rpid) #This will be the raspberry pi's identification number - since we will only be using 1 RPi, we can hardcode 0
        for key, value in sensorDictionary.items():
            xmlElement = xmlRoot.find(key)
            xmlElement.text = str(value)
        #Write and Close File
        xmlParsed.write(xmlFileName)
        xmlFile.close()

        #Send XML and wait for 60 seconds for the next interval
        sendXMLThread = Thread(target=GetAndSendXML, args=(xmlFileName,))
        sendXMLThread.start()
        timer = (time.time() - startTime) % 60
        print("XML transfer moved to a background thread...\nMain thread is now on standby for {0:.2} seconds...".format(str((60.0 - timer))))
        time.sleep(60.0 - timer)
    #while end

if __name__ == "__main__":
    print("Program Start")
    Main()