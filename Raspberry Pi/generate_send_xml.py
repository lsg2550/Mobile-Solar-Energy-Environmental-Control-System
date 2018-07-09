#########################################################################################
# gensend_xml.py (Generate and Send XML)                                                #
# Generates the XML by reading from sensors and storing the variables in an XML file,   #
# then sends the XML file to the website using an FTP connection                        #
#########################################################################################

#import
import os
import sys
import time
import json
import requests
import connect_to_ftp as CTF
import read_analog_from_adc as RAFA
from threading import Thread
from xml.etree import ElementTree
from datetime import datetime
from pytz import timezone

#Create Storage Directories
storageDirectory = "TempStorage/"
sentDirectory = "SentStorage/"
if not os.path.isdir(storageDirectory): os.mkdir(storageDirectory)
if not os.path.isdir(sentDirectory): os.mkdir(sentDirectory)

#Date & Time Format for XML
dateAndTimeFormat = "%Y-%m-%d %H:%M:%S"

#JSON Format
jsonFormat = {"photo":"0.5"}

#RaspberryPi Identification Number (rpid) & Payload for Server Confirmation
rpid = 0
pipayload = {"rpid": rpid}

def GetAndSendStatus(): #Send XML to Server
    try:
        
        for storedFile in sorted(os.listdir(storageDirectory)):
            tempFile = str(storedFile)

            if tempFile.endswith(".json"):
                fullStoragePath = os.path.join(storageDirectory, tempFile)
                fullSentPath = os.path.join(sentDirectory, tempFile)
                CTF.SendStatus(storageDirectory + tempFile)
                
                pipayload["xmlfile"] = tempFile
                serverConfirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/piconfirm.php", params=pipayload)
                print(serverConfirmation.text.strip())
                pipayload.pop("xmlfile")

                if serverConfirmation.text.strip() == "OK":
                    print("File confirmed received!")
                    os.rename(fullStoragePath, fullSentPath)
                elif serverConfirmation.text.strip() == "ERROR":
                    #os.remove(tempFile)
                    #sys.exit("Error in server processing XML file...\nDeleting file and exiting program...\nContact an administrator immediately!")
                    break
                else: break #If server did not receive or process the XML correctly, break out of the loop
    except Exception as e:
        print("Could not connect to server...\nStoring status file into {}...".format(storageDirectory))
        print(e)

    #Debug Output
    print("Background thread done!")
#GetAndSendStatus() end

def Main():
    #Program Start Time
    startTime = time.time()
    
    while True:
        #Retrieve XML Files of Thresholds set by Users
        try:
            print("Requesting threshold update from server...")
            serverThresholdConfirm = requests.get("https://remote-ecs.000webhostapp.com/index_files/pithresholdconfirm.php", params=pipayload)

            if serverThresholdConfirm.text.strip() == "OK":
                #Retrieve the XML after getting the OK from the server
                CTF.RetrieveThreshold(rpid)
                thresholdFileName = str(rpid) + ".json"

                #Tell the server that we retrieved the file
                pipayload["result"] = "OK"
                requests.get("https://remote-ecs.000webhostapp.com/index_files/piserverconfirm.php", params=pipayload)
                pipayload.pop("result")
            else:
                #Tell the server that we DID NOT retrieve the file
                pipayload["result"] = "NO"
                requests.get("https://remote-ecs.000webhostapp.com/index_files/piserverconfirm.php", params=pipayload)
                pipayload.pop("result")
                
                raise FileNotFoundError
        except Exception as e: #Assuming connection error
            print("Could not connect to server/Issue with server...")
            if os.path.exists(str(rpid) + ".json"):
                thresholdFileName = str(rpid) + ".json"
                print("Using previous thresholds...")
            else:
                thresholdFileName = "default.json"
                print("Using system default thresholds...")

        with open(thresholdFileName, "r") as thresholdfile:
            thresholds = json.loads(thresholdfile.read())
            
        thresholdVoltageLower = thresholds["voltagelower"]
        thresholdVoltageUpper = thresholds["voltageupper"]
        thresholdTemperatureLower = thresholds["temperaturelower"]
        thresholdTemperatureUpper = thresholds["temperatureupper"]
        #thresholdPhotoLower = thresholds["photolower"]
        #thresholdPhotoUpper = thresholds["photoupper"]
        thresholdSolarPanelToggle = thresholds["solartoggle"]
        thresholdExhaustToggle = thresholds["exhausttoggle"]
        #print("This is the Solar Panel Toggle: {}\nThis is the Exhaust Toggle: {}".format(thresholdSolarPanelToggle, thresholdExhaustToggle))    
        #print("Voltage Lower Threshold: {}\nVoltage Upper Threshold: {}\nTemperature Lower Threshold: {}\nTemperature Upper Threshold: {}".format(thresholdVoltageLower, thresholdVoltageUpper, thresholdTemperatureLower, thresholdTemperatureUpper))
        
        #Read from Sensors
        sensorDictionary = RAFA.ReadFromSensors(thresholdVoltageLower, thresholdVoltageUpper, thresholdTemperatureLower, thresholdTemperatureUpper)

        #Generate Timestamps
        timeStampForLog = datetime.now(timezone("UTC")).strftime(dateAndTimeFormat)
        timeStampForFileName = timeStampForLog.replace(":", "-")
        
        #Update jsonFormat
        jsonFormat["log"] = str(timeStampForLog)
        jsonFormat["rpid"] = str(rpid)
        for key, value in sensorDictionary.items():
            jsonFormat[key] = str(value)
            
        #Write and Close File
        statusFileName = storageDirectory + "status" + str(rpid) + "(" + timeStampForFileName + ").json"
        with open(statusFileName, "w+") as status:
            json.dump(jsonFormat, status, indent = 4)
            
        #Send XML in New Thread
        sendThread = Thread(target=GetAndSendStatus, args=())
        sendThread.start()

        #Wait for 60 seconds for the next read interval
        timer = (time.time() - startTime) % 60
        print("File transfer moved to a background thread...\nMain thread is now on standby for {0:.2} seconds...".format(str((60.0 - timer))))
        time.sleep(60.0 - timer)
    #while end
#Main() end

if __name__ == "__main__":
    print("Program Start")
    Main()
else:
    print("Cannot Start from Outside Script")