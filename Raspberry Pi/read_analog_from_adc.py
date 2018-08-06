#import
import RPi.GPIO as GPIO
import spidev
import time
import os

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
#GPIO.setup(exhaust. GPIO.OUT)

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
    #Debug Output
    print("Reading from sensors...")
    
    #Dictionary to hold {Sensor => Value}
    tempDictionary = {}
    
    #Read sensors
    #Channel 0 and 1 - Battery
    batteryVoltageReducedVoltage = ReadChannel(batteryVoltage)
    batteryVoltageActualVoltage = ConvertVolts(batteryVoltageReducedVoltage, 2)
    tempDictionary["BatteryVoltage"] = batteryVoltageActualVoltage
    
    batteryCurrentRead = ReadChannel(batteryCurrent)
    tempDictionary["BatteryCurrent"] = batteryCurrent
    
    #Channel 2 and 3 - Solar Panel
    solarPanelReducedVoltage = ReadChannel(solarPanelVoltage)
    solarPanelActualVoltage = ConvertVolts(solarPanelReducedVoltage, 2)
    tempDictionary["SolarPanelVoltage"] = solarPanelActualVoltage
    
    solarPanelCurrentRead = ReadChannel(solarPanelCurrent)
    tempDictionary["SolarPanelCurrent"] = solarPanelCurrentRead
    
    #if solarPanelActualVoltage >= 1: tempDictionary["solarpanel"] = "charging"
    #else: tempDictionary["solarpanel"] = "not charging"
    
    #Channel 4 - Temperature
    temperatureValue = ReadChannel(temperature) #Celcius
    tempDictionary["temperature"] = 30 #temperatureValue
    
    #Do something with read/given values
    thresholdVL = float(thresholdVoltageLower)
    thresholdVU = float(thresholdVoltageUpper)
    thresholdTL = float(thresholdTemperatureLower)
    thresholdTU = float(thresholdTemperatureUpper)
    
    if thresholdSolarPanelToggle == None:
        if batteryVoltageActualVoltage >= thresholdVU:
            tempDictionary["SolarPanel"] = "not charging"
            #Code to power off/cut off solarpanel
        elif batteryVoltageActualVoltage <= thresholdVU:
            tempDictionary["SolarPanel"] = "charging"
            #Code to power on/connect to solarpanel
    elif thresholdSolarPanelToggle == "ON":
        tempDictionary["SolarPanel"] = "charging"
        #Code to power on/connect to solarpanel
    elif thresholdSolarPanelToggle == "OFF":
        tempDictionary["SolarPanel"] = "not charging"
        #Code to power off/cut off solarpanel

    if thresholdExhaustToggle == None:       
        if temperatureValue >= thresholdTU:
            if batteryVoltageActualVoltage >= thresholdVL:
                tempDictionary["exhaust"] = "on" #Turn on exhaust
                #Code to power on exhaust
            else:
                tempDictionary["exhaust"] = "off" #Turn off exhaust
                #Code to power off exhaust
        elif temperatureValue <= thresholdTU: pass #Do Nothing (for now) - Would require turning on the exhaust and changing AC to provide warmer air
    elif thresholdSolarPanelToggle == "ON":
        tempDictionary["exhaust"] = "on" #Turn on exhaust
        #Code to power on exhaust
    elif thresholdSolarPanelToggle == "OFF":
        tempDictionary["exhaust"] = "off" #Turn off exhaust
        #Code to power off exhaust

    #Debug Output
    #print("---------------------------------------------------------")
    #print("Battery Voltage: {} ({}V)".format(batteryVoltageReducedVoltage, batteryVoltageActualVoltage))
    #print("---------------------------------------------------------")
    #print("Battery Current: {}A".format(batteryVoltageCurrentRead))
        
    #print("---------------------------------------------------------")
    #print("Solar Panel Voltage: {} ({}V)".format(solarPanelReducedVoltage, solarPanelActualVoltage))
    #print("---------------------------------------------------------")
    #print("Solar Panel Current: {}A".format(solarPanelCurrentRead))
        
    #print("---------------------------------------------------------")
    #print("Temperature: {}C ({}F)".format(temperatureValue, CelciusToFahrenheit(temperatureValue)))
    print("Done reading from sensors...")
    return tempDictionary;