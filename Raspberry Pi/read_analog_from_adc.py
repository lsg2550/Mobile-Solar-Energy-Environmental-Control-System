# import
from datetime import datetime
import RPi.GPIO as GPIO
import requests
import spidev
import serial
import time
import os
import random

# RaspberryPi Identification Number (rpid) & Payload for Server Confirmation
rpid = 0
pipayload = {"rpid": rpid}

# Initialize
delay = 1.0

# Analog Devices = Channel #
spi = spidev.SpiDev()
spi.open(0, 0)
batteryVoltage = 0  # ESU - Voltage
batteryCurrent = 1  # ESU - Current
temperatureInner = 4  # Temperature inner sensor
temperatureOuter = 5  # Temperature outer sensor

# GPIO Devices
GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)
exhaust = 22
engine = 23
# GPIO.setup(exhaust.GPIO.OUT)
# GPIO.setup(engine.GPIO.OUT)

# Serial Devices
try:
    serialGPS = serial.Serial(port = "/dev/ttyACM0", baudrate = 9600, timeout = 1)
    serialChargeController = serial.Serial(port = "/dev/ttyUSB0", baudrate = 9600, timeout = 1)
except: pass

def ReadGPS():
    tLatLon = {}
    while True:
        line = serialGPS.readline().decode("utf-8")
        data = line.split(",")
        if data[0] == "$GPRMC" and data[2] == "A": 
            # Latitude
            latGPS = float(data[3]) if data[4] != "S" else -float(data[3])
            latDeg = int(latGPS/100)
            latMin = latGPS - latDeg*100
            latAct = latDeg + (latMin/60)

            # Longitude
            lonGPS = float(data[5]) if data[6] != "W" else -float(data[5])
            lonDeg = int(lonGPS/100)
            lonMin = lonGPS - lonDeg*100
            lonAct = lonDeg + (lonMin/60)

            tLatLon["latitude"] = latAct
            tLatLon["longtitude"] = lonAct
            break
    return tLatLon

def ReadChargeController():
    tCVCCSVSC = {}
    # line = serialChargeController.readlines(10)
    return tCVCCSVSC

def ReadChannel(channel):  # Reads from given channel
    adc = spi.xfer2([1, (8 + channel) << 4, 0])
    data = ((adc[1] & 3) << 8) + adc[2]
    return data

def ConvertVolts(data, place):
    volts = (data * 3.3) / float(1023)  # TODO: Change this
    volts = round(volts, place)
    return volts

def CelciusToFahrenheit(temperatureCelcius): return ((temperatureCelcius * 9/5) + 32)
def FahrenheitToCelcius(temperatureFahrenheit): return ((temperatureFahrenheit - 32) * 5/9)

def ReadFromSensors(thresholdBattVoltageLower=None, thresholdBattVoltageUpper=None,
                    thresholdBattCurrentLower=None, thresholdBattCurrentUpper=None,
                    thresholdSoPaVoltageLower=None, thresholdSoPaVoltageUpper=None,
                    thresholdSoPaCurrentLower=None, thresholdSoPaCurrentUpper=None,
                    thresholdChCtVoltageLower=None, thresholdChCtVoltageUpper=None,
                    thresholdChCtCurrentLower=None, thresholdChCtCurrentUpper=None,
                    thresholdTemperatureLower=None, thresholdTemperatureUpper=None,
                    thresholdSolarPanelToggle=None, thresholdExhaustToggle=None):
    # Set Thresholds
    # Battery Thresholds
    thresholdBVL = float(thresholdBattVoltageLower)
    thresholdBVU = float(thresholdBattVoltageUpper)
    thresholdBCL = float(thresholdBattCurrentLower)
    thresholdBCU = float(thresholdBattCurrentUpper)
    # Solar Panel Thresholds
    thresholdSPVL = float(thresholdSoPaVoltageLower)
    thresholdSPVU = float(thresholdSoPaVoltageUpper)
    thresholdSPCL = float(thresholdSoPaCurrentLower)
    thresholdSPCU = float(thresholdSoPaCurrentUpper)
    # Charge Controller Thresholds
    thresholdCCVL = float(thresholdChCtVoltageLower)
    thresholdCCVU = float(thresholdChCtVoltageUpper)
    thresholdCCCL = float(thresholdChCtCurrentLower)
    thresholdCCCU = float(thresholdChCtCurrentUpper)
    # Temperature Thresolds
    thresholdTL = float(thresholdTemperatureLower)
    thresholdTU = float(thresholdTemperatureUpper)
    
    # Dictionary to hold {Sensor => Value}
    print("Reading from sensors...")
    tempDictionary = {}

    # Channel 0 and 1 - Battery
    # batteryVoltageRead = ReadChannel(batteryVoltage)
    # batteryCurrentRead = ReadChannel(batteryCurrent)
    batteryVoltageRead = random.randint(11, 14)
    batteryCurrentRead = random.randint(0, 1000)

    # Channel 4 and 5 - Inner and Outer Temperature Sensors
    # temperatureValueI = ReadChannel(temperatureInner) 
    # temperatureValueO = ReadChannel(temperatureOuter) 
    temperatureValueI = random.randint(0, 100)
    temperatureValueO = random.randint(0, 100)

    # Read Serially - GPS and Charge Controller
    # ccCVCCSVSC = ReadChargeController()
    # ccCVoltage = ccCVCCSVSC[0]
    # ccCCurrent = ccCVCCSVSC[1]
    ccSPVoltage = random.randint(0, 17)
    ccSPCurrent = random.randint(0, 1000)
    # ccSPVoltage = ccCVCCSVSC[2]
    # ccSPCurrent = ccCVCCSVSC[3]
    gpsLatLon = ReadGPS()
    gpsLatitude = gpsLatLon[0]
    gpsLongitude = gpsLatLon[1]

    # Check for notification purposes
    try:
        currentHour = int(datetime.now().strftime("%H")) # Uses military hours (0-23)
        if currentHour >= 9 and currentHour <= 16:
            if batteryVoltageRead <= thresholdBVL or batteryVoltageRead >= thresholdBVU:
                pipayload["noti"] = "bvoltage"
                serverConfirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=pipayload)
                # print(serverConfirmation.text.strip())
                pipayload.pop("noti")
            if batteryCurrentRead <= thresholdBCL or batteryCurrentRead >= thresholdBCU:
                pipayload["noti"] = "bcurrent"
                serverConfirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=pipayload)
                # print(serverConfirmation.text.strip())
                pipayload.pop("noti")
            if ccSPVoltage <= thresholdSPVL or ccSPVoltage >= thresholdSPVU:
                pipayload["noti"] = "spvoltage"
                serverConfirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=pipayload)
                # print(serverConfirmation.text.strip())
                pipayload.pop("noti")
            if ccSPCurrent <= thresholdSPCL or ccSPCurrent >= thresholdSPCU:
                pipayload["noti"] = "spcurrent"
                serverConfirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=pipayload)
                # print(serverConfirmation.text.strip())
                pipayload.pop("noti")
            # if ccCVoltage <= thresholdCCVL or ccCVoltage >= thresholdCCVL:
            #     pipayload["noti"] = "ccvoltage"
            #     serverConfirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=pipayload)
            #     # print(serverConfirmation.text.strip())
            #     pipayload.pop("noti")
            # if ccCCurrent <= thresholdCCCL or ccCCurrent >= thresholdCCCL:
            #     pipayload["noti"] = "cccurrent"
            #     serverConfirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=pipayload)
            #     # print(serverConfirmation.text.strip())
            #     pipayload.pop("noti")
            if temperatureValueI <= thresholdTL or temperatureValueI >= thresholdTU:
                pipayload["noti"] = "temperatureI"
                serverConfirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=pipayload)
                # print(serverConfirmation.text.strip())
                pipayload.pop("noti")
            if temperatureValueO <= thresholdTL or temperatureValueO >= thresholdTU: 
                pipayload["noti"] = "temperatureO"
                serverConfirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=pipayload)
                # print(serverConfirmation.text.strip())
                pipayload.pop("noti")
    except: pass  # Unable to connect to internet, so just disregard sending a notification

    # ESSO Operations
    # Solar Panel Operations
    if thresholdSolarPanelToggle == None:
        if batteryVoltageRead >= thresholdBVU:
            tempDictionary["solarpanel"] = "not charging"
            # Code to power/cut off solarpanel
        elif batteryVoltageRead <= thresholdBVU:
            tempDictionary["solarpanel"] = "charging"
            # Code to power-on/connect to solarpanel
    elif thresholdSolarPanelToggle == "ON":
        tempDictionary["solarpanel"] = "charging"
        # Code to power on/connect to solarpanel
    elif thresholdSolarPanelToggle == "OFF":
        tempDictionary["solarpanel"] = "not charging"
        # Code to power off/cut off solarpanel
    # Exhaust Operations
    if thresholdExhaustToggle == None:
        if temperatureValueI >= thresholdTU:  # For Hot Air -> Cold Air
            if batteryVoltageRead >= thresholdBVL:
                tempDictionary["exhaust"] = "on"
                # Code to power on exhaust
            else:
                tempDictionary["exhaust"] = "off"
                # Code to power off exhaust
        elif temperatureValueI <= thresholdTU:  # For Cold Air -> Hot Air
            pass  # Do Nothing - Would require turning on the exhaust and changing AC to provide warmer air
    elif thresholdSolarPanelToggle == "ON":
        tempDictionary["exhaust"] = "on"
        # Code to power on exhaust
    elif thresholdSolarPanelToggle == "OFF":
        tempDictionary["exhaust"] = "off"
        # Code to power off exhaust

    # Fill tempDictionary with recorded values
    tempDictionary["batteryvoltage"] = batteryVoltageRead
    tempDictionary["batterycurrent"] = batteryCurrentRead
    tempDictionary["solarpanelvoltage"] = ccSPVoltage
    tempDictionary["solarpanelcurrent"] = ccSPCurrent
    tempDictionary["temperatureinner"] = temperatureValueI
    tempDictionary["temperatureouter"] = temperatureValueO
    tempDictionary["gpslatitude"] = gpsLatitude
    tempDictionary["gpslongitude"] = gpsLongitude
    # tempDictionary["ccvoltage"] = ccVoltage
    # tempDictionary["cccurrent"] = ccCurrent

    print("Done reading from sensors...")
    return tempDictionary