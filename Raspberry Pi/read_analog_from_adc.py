#import
import RPi.GPIO as GPIO
import requests
import spidev
import time
import os
import random

#RaspberryPi Identification Number (rpid) & Payload for Server Confirmation
rpid = 0
pipayload = {"rpid": rpid}

#Initialize
delay = 1.0
#GPIO.setmode(GPIO.BCM) #GPIO Init
#GPIO.setwarnings(False)
spi = spidev.SpiDev() #ADC Init
spi.open(0, 0)

#Analog Devices -- Analog Device = Channel
batteryVoltage = 0 #ESU - Voltage
batteryCurrent = 1 #ESU - Current
solarPanelVoltage = 2 #Solar panel voltage
solarPanelCurrent = 3 #Solar panel current
temperature = 4 #Temperature sensor

#GPIO Devices
#exhaust = 22
#engine = 23
#GPIO.setup(exhaust. GPIO.OUT)
#GPIO.setup(engine.GPIO.OUT)

def ReadChannel(channel): #Reads from given channel
    adc = spi.xfer2([1, (8 + channel) << 4,0])
    data = ((adc[1] & 3) << 8) + adc[2]
    return data

def ConvertVolts(data, place):
    volts = (data * 3.3) / float(1023) #TODO: Change this
    volts = round(volts, place)
    return volts

def CelciusToFahrenheit(temperatureCelcius):
    return ((temperatureCelcius * 9/5) + 32)

def FahrenheitToCelcius(temperatureFahrenheit):
    return ((temperatureFahrenheit - 32) * 5/9)

def ReadFromSensors(thresholdVoltageLower=None, thresholdVoltageUpper=None, thresholdTemperatureLower=None, thresholdTemperatureUpper=None, thresholdPhoto=None, thresholdSolarPanelToggle=None, thresholdExhaustToggle=None):
    print("Reading from sensors...")
    
    #Dictionary to hold {Sensor => Value}
    tempDictionary = {}
    
    #Channel 0 and 1 - Battery
    #batteryVoltageReducedVoltage = ReadChannel(batteryVoltage)
    #batteryVoltageActualVoltage = ConvertVolts(batteryVoltageReducedVoltage, 2)
    #batteryCurrentRead = ReadChannel(batteryCurrent)
    batteryVoltageActualVoltage = random.randint(11, 14)
    batteryCurrentRead = random.randint(0, 1000)
    
    #Channel 2 and 3 - Solar Panel
    #solarPanelReducedVoltage = ReadChannel(solarPanelVoltage)
    #solarPanelActualVoltage = ConvertVolts(solarPanelReducedVoltage, 2)
    #solarPanelCurrentRead = ReadChannel(solarPanelCurrent)
    solarPanelActualVoltage = random.randint(0, 17)
    solarPanelCurrentRead = random.randint(0, 1000)
    
    #Channel 4 - Temperature
    #temperatureValue = ReadChannel(temperature) #Celcius
    temperatureValue = random.randint(0, 100)
    
    #Do something with given threshold values
    thresholdVL = float(thresholdVoltageLower)
    thresholdVU = float(thresholdVoltageUpper)
    thresholdTL = float(thresholdTemperatureLower)
    thresholdTU = float(thresholdTemperatureUpper)
    
    #Check for notification purposes
    if batteryVoltageActualVoltage <= thresholdVL:
        pipayload["noti"] = "voltage"
        serverConfirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=pipayload)
        print(serverConfirmation.text.strip())
        pipayload.pop("noti")
        #Send Start Engine Signal

    #Fill tempDictionary with recorded values
    tempDictionary["BatteryVoltage"] = batteryVoltageActualVoltage
    tempDictionary["BatteryCurrent"] = batteryCurrentRead
    tempDictionary["SolarPanelVoltage"] = solarPanelActualVoltage
    tempDictionary["SolarPanelCurrent"] = solarPanelCurrentRead
    tempDictionary["temperature"] = temperatureValue

    #Perform Operations with ESSO
    #Solar Panel Operations
    if thresholdSolarPanelToggle == None:
        if batteryVoltageActualVoltage >= thresholdVU:
            tempDictionary["SolarPanel"] = "not charging"
            #Code to power/cut off solarpanel
        elif batteryVoltageActualVoltage <= thresholdVU:
            tempDictionary["SolarPanel"] = "charging"
            #Code to power-on/connect to solarpanel
    elif thresholdSolarPanelToggle == "ON":
        tempDictionary["SolarPanel"] = "charging"
        #Code to power on/connect to solarpanel
    elif thresholdSolarPanelToggle == "OFF":
        tempDictionary["SolarPanel"] = "not charging"
        #Code to power off/cut off solarpanel

    #Exhaust Operations
    if thresholdExhaustToggle == None:       
        if temperatureValue >= thresholdTU: #For Hot Air -> Cold Air
            if batteryVoltageActualVoltage >= thresholdVL:
                tempDictionary["exhaust"] = "on"
                #Code to power on exhaust
            else:
                tempDictionary["exhaust"] = "off"
                #Code to power off exhaust
        elif temperatureValue <= thresholdTU: #For Cold Air -> Hot Air
            pass #Do Nothing - Would require turning on the exhaust and changing AC to provide warmer air
    elif thresholdSolarPanelToggle == "ON":
        tempDictionary["exhaust"] = "on"
        #Code to power on exhaust
    elif thresholdSolarPanelToggle == "OFF":
        tempDictionary["exhaust"] = "off"
        #Code to power off exhaust
        
    print("Done reading from sensors...")
    return tempDictionary;