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
battery = 0 #car battery
solar_panel = 1 #solar panel
temperature = 2 #temperature sensor

#GPIO Devices
#exhaust = 22
#solar_panel = 
#camera = 
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

def ReadFromSensors(thresholdVoltagelower=None, thresholdVoltageUpper=None, thresholdTemperatureLower=None, thresholdTemperatureUpper=None, thresholdPhotoLower=None, thresholdPhotoUpper=None, thresholdSolarPanelToggle=None, thresholdExhaustToggle=None):
    #Debug Output
    print("Reading from sensors...")
    
    #Dictionary to hold {Sensor => Value}
    tempDictionary = {}
    
    #Read sensors
    #Channel 0 - Battery
    batteryReducedVoltage = ReadChannel(battery)
    batteryActualVoltage = ConvertVolts(batteryReducedVoltage, 2)
    tempDictionary["battery"] = batteryActualVoltage
    
    #Channel 1 - Solar Panel
    solarPanelReducedVoltage = ReadChannel(solar_panel)
    solarPanelActualVoltage = ConvertVolts(solarPanelReducedVoltage, 2)
    tempDictionary["solarpanelvalue"] = solarPanelActualVoltage
    #if solarPanelActualVoltage >= 1: tempDictionary["solarpanel"] = "charging"
    #else: tempDictionary["solarpanel"] = "not charging"
    
    #Channel 2 - Temperature
    temperatureValue = ReadChannel(temperature) #Celcius
    tempDictionary["temperature"] = 30 #temperatureValue

    #Channel 3 - Exhaust
    #Read status of exhaust

    #Channel 4 - Camera/Photo
    #Take a snapshot
    
    #Do something with read/given values
    if thresholdSolarPanelToggle == None:
        if batteryActualVoltage >= thresholdVoltageUpper:
            tempDictionary["solarpanel"] = "not charging"
            #Code to power off/cut off solarpanel
        elif batteryActualVoltage <= thresholdVoltageUpper:
            tempDictionary["solarpanel"] = "charging"
            #Code to power on/connect to solarpanel
    elif thresholdSolarPanelToggle == "ON":
        tempDictionary["solarpanel"] = "charging"
        #Code to power on/connect to solarpanel
    elif thresholdSolarPanelToggle == "OFF":
        tempDictionary["solarpanel"] = "not charging"
        #Code to power off/cut off solarpanel

    if thresholdExhaustToggle == None:       
        if temperatureValue >= thresholdTemperatureUpper:
            if batteryActualVoltage >= thresholdVoltagelower:
                tempDictionary["exhaust"] = "on" #Turn on exhaust
                #Code to power on exhaust
            else:
                tempDictionary["exhaust"] = "off" #Turn off exhaust
                #Code to power off exhaust
        elif temperatureValue <= thresholdTemperatureUpper: pass #Do Nothing (for now) - Would require turning on the exhaust and changing AC to provide warmer air
    elif thresholdSolarPanelToggle == "ON":
        tempDictionary["exhaust"] = "on" #Turn on exhaust
        #Code to power on exhaust
    elif thresholdSolarPanelToggle == "OFF":
        tempDictionary["exhaust"] = "off" #Turn off exhaust
        #Code to power off exhaust

    #Debug Output
    #print("---------------------------------------------------------")
    #print("Battery Voltage: {} ({}V)".format(batteryReducedVoltage, batteryActualVoltage))
    #print("---------------------------------------------------------")
    #print("Solar Panel Voltage: {} ({}V)".format(solarPanelReducedVoltage, solarPanelActualVoltage))
    #print("---------------------------------------------------------")
    #print("Temperature: {}C ({}F)".format(temperatureValue, CelciusToFahrenheit(temperatureValue)))
    print("Done reading from sensors...")
    return tempDictionary;