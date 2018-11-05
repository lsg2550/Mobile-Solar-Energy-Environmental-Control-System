# import
from datetime import datetime
from threading import Thread
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
notificationThread = None
TEMPERATURE_NO_ERROR = 0
TEMPERATURE_DATA_MISSING = 1
TEMPERATURE_CHECKSUM_ERROR = 2
GPS_NO_ERROR = 0
GPS_COORD_INACCESSIBLE = 1

# Analog Devices = Channel #
spi = spidev.SpiDev()
spi.open(0, 0)
batteryVoltage = 0  # ESU - Voltage
batteryCurrent = 1  # ESU - Current

# GPIO Devices = GPIO Pin #
GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)
GPIO.cleanup()
DHT11 = 22
# exhaust = 23
# engine = 24
# GPIO.setup(exhaust, GPIO.OUT, pull_up_down = GPIO.PUD_DOWN)
# GPIO.setup(engine, GPIO.OUT, pull_up_down = GPIO.PUD_DOWN)

# Serial Devices
try: serialGPS = serial.Serial(port = "/dev/ttyACM0", baudrate = 9600, timeout = 1)
except: pass
try: serialChargeController = serial.Serial(port = "/dev/ttyUSB0", baudrate = 9600, timeout = 1)
except: pass

def ReadGPS():
    # Init
    global GPS_NO_ERROR
    global GPS_COORD_INACCESSIBLE
    timeoutMaxCount = 100
    timeoutCounter = 0
    tLatLon = []
    
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
def ReadTemperatureSensor(sensorPin):
    # Init
    global DHT11
    global TEMPERATURE_NO_ERROR
    global TEMPERATURE_DATA_MISSING
    global TEMPERATURE_CHECKSUM_ERROR

    # 'Open' sensor to gather data
    GPIO.setup(DHT11, GPIO.OUT)
    GPIO.output(DHT11, GPIO.HIGH)
    time.sleep(0.025)
    GPIO.output(DHT11, GPIO.LOW)
    time.sleep(0.020)
    GPIO.setup(DHT11, GPIO.IN, pull_up_down = GPIO.PUD_UP)

    # Read data
    timeoutMaxCount = 100
    timeoutCounter = 0
    tempPrev = -1
    data = []
    while True:
        tempCurr = GPIO.input(DHT11)
        data.append(tempCurr)
        if tempPrev != tempCurr:
            timeoutCounter = 0
            tempPrev = tempCurr
        else:
            timeoutCounter += 1
            if timeoutCounter > timeoutMaxCount: break

    # Find Pull-Up-Down Position Lengths
    ST_INIT_PUD_DOWN = 1
    ST_INIT_PUD_UP = 2
    ST_DATA_FIRST_PUD_DOWN = 3
    ST_DATA_PUD_UP = 4
    ST_DATA_PUD_DOWN = 5
    initialState = ST_INIT_PUD_DOWN
    listOfStateLengths = []
    lengthCounter = 0
    for i in range(len(data)):
        currentPUDPosition = data[i]
        lengthCounter += 1
        if initialState == ST_INIT_PUD_DOWN:
            if currentPUDPosition == GPIO.LOW: initialState = ST_INIT_PUD_UP
            continue
        if initialState == ST_INIT_PUD_UP:
            if currentPUDPosition == GPIO.HIGH: initialState = ST_DATA_FIRST_PUD_DOWN
            continue
        if initialState == ST_DATA_FIRST_PUD_DOWN:
            if currentPUDPosition == GPIO.LOW: initialState = ST_DATA_PUD_UP
            continue
        if initialState == ST_DATA_PUD_UP:
            if currentPUDPosition == GPIO.HIGH:
                initialState = ST_DATA_PUD_DOWN
                lengthCounter = 0
            continue
        if initialState == ST_DATA_PUD_DOWN:
            if currentPUDPosition == GPIO.LOW:
                initialState = ST_DATA_PUD_UP
                listOfStateLengths.append(lengthCounter)
            continue
    if len(listOfStateLengths) != 40: return [TEMPERATURE_DATA_MISSING, 0, 0]
    
    # Find Bits
    shortestPUD = float('Inf')
    longestPUD = 0
    listOfBits = []
    for i in range(0, len(listOfStateLengths)):
        length = listOfStateLengths[i]
        if length < shortestPUD: shortestPUD = length
        if length > longestPUD: longestPUD = length
    medianPUDPosition = shortestPUD + ((longestPUD - shortestPUD) / 2)
    for i in range(0, len(listOfStateLengths)):
        if listOfStateLengths[i] > medianPUDPosition: bit = True
        else: bit = False
        listOfBits.append(bit)
    
    # Convert Bits to Bytes
    listOfBytes = []
    byte = 0
    for i in range(0, len(listOfBits)):
        byte = byte << 1
        if listOfBits[i]: byte = byte | 1
        else: byte = byte | 0
        if (i + 1) % 8 == 0:
            listOfBytes.append(byte)
            byte = 0
    checksum = listOfBytes[0] + listOfBytes[1] + listOfBytes[2] + listOfBytes[3] & 255
    if listOfBytes[4] != checksum: return [TEMPERATURE_CHECKSUM_ERROR, 0, 0]

    # Return Status, Temperature, Humidity
    return [TEMPERATURE_NO_ERROR, listOfBytes[2], listOfBytes[0]]
def ReadADCChannel(channel):
    adc = spi.xfer2([1, (8 + channel) << 4, 0])
    data = ((adc[1] & 3) << 8) + adc[2]
    return data

def CheckAndNotify(batteryVoltageRead, batteryCurrentRead,
                   ccSPVoltage, ccSPCurrent,
                   ccCVoltage, ccCCurrent,
                   temperatureValueI, temperatureValueO,
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
    global notificationThread
    global TEMPERATURE_NO_ERROR
    global TEMPERATURE_DATA_MISSING
    global TEMPERATURE_CHECKSUM_ERROR
    global GPS_NO_ERROR
    global GPS_COORD_INACCESSIBLE
    
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
    # batteryVoltageRead = ReadADCChannel(batteryVoltage)
    # batteryCurrentRead = ReadADCChannel(batteryCurrent)
    batteryVoltageRead = random.randint(11, 14)
    batteryCurrentRead = random.randint(0, 1000)

    # GPIO BCM Format Pin(s) 22 - Inner and Outer Temperature Sensors
    temperatureValueI = ReadTemperatureSensor(DHT11)
    temperatureValueO = ReadTemperatureSensor(DHT11)

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
    gpsLatLon = ReadGPS()

    # Check for notification purposes
    if notificationThread == None or not notificationThread.isAlive():
        notificationThread = Thread(target=CheckAndNotify, args=(batteryVoltageRead, batteryCurrentRead, ccSPVoltage, ccSPCurrent, ccCVoltage, ccCCurrent, temperatureValueI, temperatureValueO, thresholdBVL, thresholdBVU, thresholdBCL, thresholdBCU, thresholdSPVL, thresholdSPVU, thresholdSPCL, thresholdSPCU, thresholdCCVL, thresholdCCVU, thresholdCCCL, thresholdCCCU, thresholdTIL, thresholdTIU, thresholdTOL, thresholdTOU, ))
        notificationThread.start()

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
        if temperatureValueI >= thresholdTIU:  # For Hot Air -> Cold Air
            if batteryVoltageRead >= thresholdBVL:
                tempDictionary["exhaust"] = "on"
                # Code to power on exhaust
            else:
                tempDictionary["exhaust"] = "off"
                # Code to power off exhaust
        elif temperatureValueI <= thresholdTIU:  # For Cold Air -> Hot Air
            pass  # Do Nothing - Would require turning on the exhaust and changing AC to provide warmer air
    elif thresholdSolarPanelToggle == "ON":
        tempDictionary["exhaust"] = "on"
        # Code to power on exhaust
    elif thresholdSolarPanelToggle == "OFF":
        tempDictionary["exhaust"] = "off"
        # Code to power off exhaust

    # Populate tempDictionary with recorded values
    tempDictionary["batteryvoltage"] = batteryVoltageRead
    tempDictionary["batterycurrent"] = batteryCurrentRead
    tempDictionary["solarpanelvoltage"] = ccSPVoltage
    tempDictionary["solarpanelcurrent"] = ccSPCurrent
    tempDictionary["chargecontrollervoltage"] = ccCVoltage
    tempDictionary["chargecontrollercurrent"] = ccCCurrent
    tempDictionary["temperatureinner"] = temperatureValueI[1]
    tempDictionary["temperatureouter"] = temperatureValueO[1]
    tempDictionary["humidityinner"] = temperatureValueI[2]
    tempDictionary["humidityouter"] = temperatureValueO[2]
    tempDictionary["gps"] = [gpsLatLon[1]]
    tempDictionary["gps"].append(gpsLatLon[2])

    print("Done reading from sensors...")
    return tempDictionary