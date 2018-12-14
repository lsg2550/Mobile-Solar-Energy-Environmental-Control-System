# import
from datetime import datetime
from threading import Thread
import RPi.GPIO as GPIO
import Adafruit_DHT
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
NOTIFICATION_THREAD = None
GPS_NO_ERROR = 0
GPS_COORD_INACCESSIBLE = 1
DHT11_SENSOR = Adafruit_DHT.DHT11

# Analog Devices = Channel #
spi = spidev.SpiDev()
spi.open(0, 0)
batteryVoltage = 2  # ESU - Voltage
batteryCurrent = 1  # ESU - Current

# GPIO Devices = GPIO Pin #
GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)
GPIO.cleanup()
DHT11_I = 22
DHT11_O = 17

# Previous Temperature/Humidity/GPS Values
prevTempValI = 0
prevHumiValI = 0
prevTempValO = 0
prevHumiValO = 0
prevLatitude = 0
prevLongitude = 0

# Serial Devices
try: serialGPS = serial.Serial(port = "/dev/ttyACM0", baudrate = 9600, timeout = 1)
except Exception as e: print(e)

def ReadGPS():
    # Init
    global GPS_NO_ERROR
    global GPS_COORD_INACCESSIBLE
    timeoutMaxCount = 100
    timeoutCounter = 0
    tLatLon = [None, None, None]
    
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

            tLatLon[0] = GPS_NO_ERROR
            tLatLon[1] = latAct
            tLatLon[2] = lonAct
            break
        # Test for Timeout - May be caused by the GPS module not being able to detect its location   
        timeoutCounter += 1
        if timeoutCounter > timeoutMaxCount:
            tLatLon[0] = GPS_COORD_INACCESSIBLE
            tLatLon[1] = latAct
            tLatLon[2] = lonAct
            break
    return tLatLon
def ReadChargeController():
    tCVCCSVSC = {}
    # line = serialChargeController.readlines(10)
    return tCVCCSVSC
def ReadADCChannel(channel):
    adc = spi.xfer2([1, (8 + channel) << 4, 0])
    data = ((adc[1] & 3) << 8) + adc[2]
    return data
def CheckAndNotify(batteryVoltageRead, batteryCurrentRead,
                   ccSPVoltage, ccSPCurrent,
                   ccCVoltage, ccCCurrent,
                   temperatureValueI, temperatureValueO,
                   humidityValueI, humidityValueO
                   thresholdBVL, thresholdBVU,
                   thresholdBCL, thresholdBCU,
                   thresholdSPVL, thresholdSPVU,
                   thresholdSPCL, thresholdSPCU,
                   thresholdCCVL, thresholdCCVU,
                   thresholdCCCL, thresholdCCCU,
                   thresholdTIL, thresholdTIU,
                   thresholdTOL, thresholdTOU):
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
            if ccCVoltage <= thresholdCCVL or ccCVoltage >= thresholdCCVU:
                pipayload["noti"] = "ccvoltage"
                serverConfirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=pipayload)
                # print(serverConfirmation.text.strip())
                pipayload.pop("noti")
            if ccCCurrent <= thresholdCCCL or ccCCurrent >= thresholdCCCU:
                pipayload["noti"] = "cccurrent"
                serverConfirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=pipayload)
                # print(serverConfirmation.text.strip())
                pipayload.pop("noti")
            if temperatureValueI <= thresholdTIL or temperatureValueI >= thresholdTIU:
                pipayload["noti"] = "temperatureI"
                serverConfirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=pipayload)
                # print(serverConfirmation.text.strip())
                pipayload.pop("noti")
            if temperatureValueO <= thresholdTOL or temperatureValueO >= thresholdTOU: 
                pipayload["noti"] = "temperatureO"
                serverConfirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=pipayload)
                # print(serverConfirmation.text.strip())
                pipayload.pop("noti")
            if humidityValueI <= thresholdTIL or humidityValueI >= thresholdTIU:
                pipayload["noti"] = "temperatureI"
                serverConfirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=pipayload)
                # print(serverConfirmation.text.strip())
                pipayload.pop("noti")
            if humidityValueO <= thresholdTOL or humidityValueO >= thresholdTOU: 
                pipayload["noti"] = "temperatureO"
                serverConfirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=pipayload)
                # print(serverConfirmation.text.strip())
                pipayload.pop("noti")
    except: pass  # Unable to connect to internet, so just disregard sending a notification
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
                    thresholdTemperatureInnerLower=None, thresholdTemperatureInnerUpper=None,
                    thresholdTemperatureOuterLower=None, thresholdTemperatureOuterUpper=None,
                    thresholdSolarPanelToggle=None, thresholdExhaustToggle=None):
    # Init - Global Var 
    global NOTIFICATION_THREAD
    global DHT11_SENSOR
    global GPS_NO_ERROR
    global GPS_COORD_INACCESSIBLE
    global prevTempValI
    global prevHumiValI
    global prevTempValO
    global prevHumiValO
    global prevLatitude
    global prevLongitude
    
    # Thresholds
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
    thresholdTIL = float(thresholdTemperatureInnerLower)
    thresholdTIU = float(thresholdTemperatureInnerUpper)
    thresholdTOL = float(thresholdTemperatureOuterLower)
    thresholdTOU = float(thresholdTemperatureOuterUpper)
    
    # Dictionary to hold {Sensor => Value}
    print("Reading from sensors...")
    tempDictionary = {}

    # ADC Channel 0 and 1 - Battery
    batteryVoltageRead = ReadADCChannel(batteryVoltage) # From Resistor Network - Circuit Diagram
    # batteryCurrentRead = ReadADCChannel(batteryCurrent) # From OpAmp
    batteryCurrentRead = random.randint(0, 1000)

    # Inner and Outer Temperature Sensors
    humidityInner, temperatureInner = Adafruit_DHT.read(DHT11_SENSOR, DHT11_I)
    humidityOuter, temperatureOuter = Adafruit_DHT.read(DHT11_SENSOR, DHT11_O)
    if temperatureInner is not None: prevTempValI = temperatureInner
    if humidityInner is not None: prevHumiValI = humidityInner
    if temperatureOuter is not None: prevTempValO = temperatureOuter
    if humidityOuter is not None: prevHumiValO = humidityOuter

    # Read Serially - GPS and Charge Controller
    # ccCVCCSVSC = ReadChargeController()
    ccCVoltage = random.randint(0, 20)
    ccCCurrent = random.randint(0, 10000)
    # ccCVoltage = ccCVCCSVSC[0]
    # ccCCurrent = ccCVCCSVSC[1]
    ccSPVoltage = random.randint(0, 17)
    ccSPCurrent = random.randint(0, 1000)
    # ccSPVoltage = ccCVCCSVSC[2]
    # ccSPCurrent = ccCVCCSVSC[3]
    try:  gpsLatLon = ReadGPS()
    except Exception as e: gpsLatLon = [GPS_COORD_INACCESSIBLE, 0, 0]

    # Check for notification purposes
    if NOTIFICATION_THREAD == None or not NOTIFICATION_THREAD.isAlive():
        NOTIFICATION_THREAD = Thread(target=CheckAndNotify, args=(batteryVoltageRead, batteryCurrentRead, ccSPVoltage, ccSPCurrent, ccCVoltage, ccCCurrent, temperatureInner, temperatureOuter, humidityInner, humidityOuter, thresholdBVL, thresholdBVU, thresholdBCL, thresholdBCU, thresholdSPVL, thresholdSPVU, thresholdSPCL, thresholdSPCU, thresholdCCVL, thresholdCCVU, thresholdCCCL, thresholdCCCU, thresholdTIL, thresholdTIU, thresholdTOL, thresholdTOU, ))
        NOTIFICATION_THREAD.start()

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
        if temperatureValueI[1] >= thresholdTIU:  # For Hot Air -> Cold Air
            if batteryVoltageRead >= thresholdBVL:
                tempDictionary["exhaust"] = "on"
                # Code to power on exhaust
            else:
                tempDictionary["exhaust"] = "off"
                # Code to power off exhaust
        elif temperatureValueI[1] <= thresholdTIU:  # For Cold Air -> Hot Air
            pass  # Do Nothing - Would require turning on the exhaust and changing AC to provide warmer air
    elif thresholdSolarPanelToggle == "ON":
        tempDictionary["exhaust"] = "on"
        # Code to power on exhaust
    elif thresholdSolarPanelToggle == "OFF":
        tempDictionary["exhaust"] = "off"
        # Code to power off exhaust

    # Populate tempDictionary with recorded values
    # Battery Values
    tempDictionary["batteryvoltage"] = batteryVoltageRead
    tempDictionary["batterycurrent"] = batteryCurrentRead
    # Solar Panel Values
    tempDictionary["solarpanelvoltage"] = ccSPVoltage
    tempDictionary["solarpanelcurrent"] = ccSPCurrent
    # Charge Controller Values
    tempDictionary["chargecontrollervoltage"] = ccCVoltage
    tempDictionary["chargecontrollercurrent"] = ccCCurrent
    
    # Temperature Values
    if temperatureInner is None: tempDictionary["temperatureinner"] = prevTempValI
    else: tempDictionary["temperatureinner"] = temperatureInner
    if humidityInner is None: tempDictionary["humidityinner"] = prevHumiValI
    else: tempDictionary["humidityinner"] = humidityInner
    if temperatureOuter is None: tempDictionary["temperatureouter"] = prevTempValO
    else: tempDictionary["temperatureouter"] = temperatureOuter
    if humidityOuter is None: tempDictionary["humidityouter"] = prevHumiValO
    else: tempDictionary["humidityouter"] = humidityOuter
        
    # GPS Values
    if gpsLatLon[0] == GPS_NO_ERROR:
        tempDictionary["gps"] = [gpsLatLon[1]]
        tempDictionary["gps"].append(gpsLatLon[2])
        prevLatitude = gpsLatLon[1]
        prevLongitude = gpsLatLon[2]
    else:
        tempDictionary["gps"] = [prevLatitude]
        tempDictionary["gps"].append(prevLongitude)

    # Return
    print("Done reading from sensors...")
    return tempDictionary