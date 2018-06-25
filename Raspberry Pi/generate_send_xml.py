#########################################################################################
# gensend_xml.py (Generate and Send XML)                                                #
# Generates the XML by reading from sensors and storing the variables in an XML file,   #
# then sends the XML file to the website using an FTP connection                        #
#########################################################################################

#import
import os
import sys
import time
import requests
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

#XML Format
xmlFormat = '''
<currentstatus>
    <log></log>
    <temperature></temperature>
    <battery></battery>
    <solarpanel></solarpanel>
    <solarpanelvalue></solarpanelvalue>
    <exhaust>on</exhaust>
    <photo>0.5</photo>
    <rpid></rpid>
</currentstatus>
'''

#RaspberryPi Identification Number (rpid) & Payload for Server Confirmation
rpid = 0
pipayload = {"rpid": rpid}

def GetAndSendXML(xmlFileName): #Send XML to Server
    try:
        for storedFile in os.listdir(xmlStorageDirectory):
            tempFile = str(storedFile)

            if tempFile.endswith(".xml"):
                CTF.SendXML(xmlStorageDirectory + tempFile)

                pipayload["xmlfile"] = tempFile
                serverConfirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/piconfirm.php", params=pipayload)
                #print(serverConfirmation.text.strip())
                pipayload.pop("xmlfile")

                if serverConfirmation.text.strip() == "OK":
                    print("XML file confirmed received!")
                    os.rename(xmlStorageDirectory + tempFile, xmlSentDirectory + tempFile)
                elif serverConfirmation.text.strip() == "ERROR":
                    #os.remove(tempFile)
                    #sys.exit("Error in server processing XML file...\nDeleting file and exiting program...\nContact an administrator immediately!")
                    break
                else: break #If server did not receive or process the XML correctly, break out of the loop
    except Exception as e:
        print("Could not connect to server...\nStoring XML into {}...".format(xmlStorageDirectory))
        #print(e)

    #Debug Output
    print("Background thread done!")
#GetAndSendXML() end

def Main():
    #Program Start Time
    startTime = time.time()
    
    while True:
        #Init - Threshold File Name
        thresholdFileName = ""

        #Retrieve XML Files of Thresholds set by Users
        try:
            print("Requesting threshold update from server...")
            serverThresholdConfirm = requests.get("https://remote-ecs.000webhostapp.com/index_files/pithresholdconfirm.php", params=pipayload)

            if serverThresholdConfirm.text.strip() == "OK":
                CTF.RetrieveXML(rpid)
                thresholdFileName = str(rpid) + ".xml"
            else:
                print("Issue with server request...")
                if os.path.exists(str(rpid) + ".xml"):
                    thresholdFileName = str(rpid) + ".xml"
                    print("Using previous thresholds...")
                else:
                    thresholdFileName = "default.xml"
                    print("Using system default thresholds...")
        except Exception as e: #Assuming connection error
            print("Could not connect to server...")
            if os.path.exists(str(rpid) + ".xml"):
                thresholdFileName = str(rpid) + ".xml"
                print("Using previous thresholds...")
            else:
                thresholdFileName = "default.xml"
                print("Using system default thresholds...")
            #print(e)

        thresholdFile = open(thresholdFileName, "r")
        thresholdParsed = ElementTree.parse(thresholdFile)
        thresholdRoot = thresholdParsed.getroot()
        thresholdVoltageLower = thresholdRoot.find(".//Battery/voltagelower").text
        thresholdVoltageUpper = thresholdRoot.find(".//Battery/voltageupper").text
        thresholdTemperatureLower = thresholdRoot.find(".//Temperature/temperaturelower").text
        thresholdTemperatureUpper = thresholdRoot.find(".//Temperature/temperatureupper").text
        #thresholdPhotoLower = thresholdRoot.find(".//Photo/photolower").text
        #thresholdPhotoUpper = thresholdRoot.find(".//Photo/photoupper").text
        thresholdSolarPanelToggle = thresholdRoot.find(".//SolarPanel/toggle").text
        thresholdExhaustToggle = thresholdRoot.find(".//Exhaust/toggle").text
        #print("This is the Solar Panel Toggle: {}\nThis is the Exhaust Toggle: {}".format(thresholdSolarPanelToggle, thresholdExhaustToggle))    
        #print("Voltage Lower Threshold: {}\nVoltage Upper Threshold: {}\nTemperature Lower Threshold: {}\nTemperature Upper Threshold: {}".format(thresholdVoltageLower, thresholdVoltageUpper, thresholdTemperatureLower, thresholdTemperatureUpper))
        
        #Generate Timestamps
        timeStampForLog = datetime.now(timezone("UTC")).strftime(dateAndTimeFormat)
        timeStampForFileName = timeStampForLog.replace(":", "-")
        
        #Create File & Create XML Element Tree Object
        xmlFileName = xmlStorageDirectory + "status" + str(rpid) + "(" + timeStampForFileName + ").xml"
        xmlFile = open(xmlFileName, "w+")
        xmlParsed = ElementTree.ElementTree(ElementTree.fromstring(xmlFormat))
        xmlRoot = xmlParsed.getroot()

        #Read from Sensors
        sensorDictionary = RAFA.ReadFromSensors(thresholdVoltageLower, thresholdVoltageUpper, thresholdTemperatureLower, thresholdTemperatureUpper)
        xmlLogElement = xmlRoot.find("log")
        xmlRpidElement = xmlRoot.find("rpid")
        xmlLogElement.text = str(timeStampForLog)
        xmlRpidElement.text = str(rpid)
        for key, value in sensorDictionary.items():
            xmlElement = xmlRoot.find(key)
            xmlElement.text = str(value)

        #Write and Close File
        xmlParsed.write(xmlFileName)
        xmlFile.close()

        #Send XML in New Thread
        sendXMLThread = Thread(target=GetAndSendXML, args=(xmlFileName,))
        sendXMLThread.start()

        #Wait for 60 seconds for the next read interval
        timer = (time.time() - startTime) % 60
        print("XML transfer moved to a background thread...\nMain thread is now on standby for {0:.2} seconds...".format(str((60.0 - timer))))
        time.sleep(60.0 - timer)
    #while end
#Main() end

if __name__ == "__main__":
    print("Program Start")
    Main()
else:
    print("Cannot Start from Outside Script")