#import
import os
import sys
import time
import json
import requests
import connect_to_ftp as CTF
import read_analog_from_adc as RAFA
import motion_detection as MD
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
                #print(serverConfirmation.text.strip())
                pipayload.pop("xmlfile")

                if serverConfirmation.text.strip() == "OK":
                    print("File confirmed received!")
                    os.rename(fullStoragePath, fullSentPath)
                elif serverConfirmation.text.strip() == "ERROR":
                    #sys.exit("Error in server processing XML file...\nDeleting file and exiting program...\nContact an administrator immediately!")
                    break
                else: break #If server did not receive or process the XML correctly, break out of the loop
    except Exception as e:
        print("Could not connect to server...\nStoring status file into {}...".format(storageDirectory))
        print(e)

    #Debug Output
    print("Status background thread done!")
#GetAndSendStatus() end

def GetAndSendImages():
    try:
        detectionDirContents = sorted(os.listdir(MD.detectionDir))
        for storedImages in detectionDirContents:
            if detectionDirContents[-1] == storedImages: break #In the chance that the last folder is still being filled with images, we don't send its contents yet
            tempFileFP = os.path.join(MD.detectionDir, storedImages)
            for root, subfolders, files in sorted(os.walk(tempFileFP)): CTF.SendImages(root, files)
    except Exception as e:
        print("Could not connect to server...\nImages were not sent")
        print(e)

    #Debug Output
    print("Images background thread done!")
#GetAndSendImages() end

def Main():
    #Program Start Time
    startTime = time.time()
    sendThread = None #Thread for sending XML/JSON
    sendImagesThread = None #Thread for sending detection images
    
    while True:
        ###########################################################################################################################################
        try: #Retrieve XML Files of Thresholds set by Users
            print("Requesting threshold update from server...")
            serverThresholdConfirm = requests.get("https://remote-ecs.000webhostapp.com/index_files/pithresholdconfirm.php", params=pipayload)
            
            if serverThresholdConfirm.text.strip() == "OK": #Retrieve the XML after getting the OK from the server
                CTF.RetrieveThreshold(rpid)
                thresholdFileName = str(rpid) + ".json"

                #Tell the server that we retrieved the file
                pipayload["result"] = "OK"
                requests.get("https://remote-ecs.000webhostapp.com/index_files/piserverconfirm.php", params=pipayload)
                pipayload.pop("result")
            else: #Tell the server that we DID NOT retrieve the file
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

        with open(thresholdFileName, "r") as thresholdfile: thresholds = json.loads(thresholdfile.read())
        thresholdVoltageLower = thresholds["voltagelower"]
        thresholdVoltageUpper = thresholds["voltageupper"]
        thresholdTemperatureLower = thresholds["temperaturelower"]
        thresholdTemperatureUpper = thresholds["temperatureupper"]
        thresholdPhoto = thresholds["photofps"]
        thresholdSolarPanelToggle = thresholds["solartoggle"] if "solartoggle" in thresholds else None
        thresholdExhaustToggle = thresholds["exhausttoggle"] if "exhausttoggle" in thresholds else None
        ###########################################################################################################################################

        #Read from Sensors
        sensorDictionary = RAFA.ReadFromSensors(thresholdVoltageLower, thresholdVoltageUpper, thresholdTemperatureLower, thresholdTemperatureUpper)

        #Generate Timestamps
        timeStampForLog = datetime.now(timezone("UTC")).strftime(dateAndTimeFormat)
        timeStampForFileName = timeStampForLog.replace(":", "-")
        
        #Update jsonFormat
        jsonFormat["log"] = str(timeStampForLog)
        jsonFormat["rpid"] = str(rpid)
        for key, value in sensorDictionary.items(): jsonFormat[key] = str(value)
            
        #Write and Close File
        statusFileName = storageDirectory + "status" + str(rpid) + "(" + timeStampForFileName + ").json"
        with open(statusFileName, "w+") as status: json.dump(jsonFormat, status, indent = 4)
            
        #Send XML in new thread
        if sendThread == None or not sendThread.isAlive():
            sendThread = Thread(target=GetAndSendStatus, args=())
            sendThread.start()

        #Send images in new thread
        if sendImagesThread == None or not sendImagesThread.isAlive():
            sendImagesThread = Thread(target=GetAndSendImages, args=())
            sendImagesThread.start()

        #Wait for 60 seconds for the next read interval
        timer = (time.time() - startTime) % 60
        print("File transfer moved to a background thread...\nMain thread is now on standby for {0:.2} seconds...".format(str((60.0 - timer))))
        time.sleep(60.0 - timer)
    #while end
#Main() end

if __name__ == "__main__":
    print("Program Start")
    Main()