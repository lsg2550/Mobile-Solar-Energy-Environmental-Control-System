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
exhaust = 22
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

def ReadFromSensors(thresholdvoltagelower=None, thresholdvoltageupper=None, thresholdtemperaturelower=None, thresholdtemperatureupper=None):
    #Debug Output
    print("Reading from sensors...")
    
    #Dictionary to hold {Sensor => Value}
    tempDictionary = {}
    
    #Channel 0
    batteryReducedVoltage = ReadChannel(battery)
    batteryActualVoltage = ConvertVolts(batteryReducedVoltage, 2)
    tempDictionary["battery"] = batteryActualVoltage
    
    #Channel 1
    solarPanelReducedVoltage = ReadChannel(solar_panel)
    solarPanelActualVoltage = ConvertVolts(solarPanelReducedVoltage, 2)
    tempDictionary["solarpanelvalue"] = solarPanelActualVoltage
    '''
    if batteryActualVoltage >= thresholdvoltageupper:
        #Disable Solar Panel?
        pass
    '''
    if solarPanelActualVoltage >= 1:
        tempDictionary["solarpanel"] = "charging"
    else:
        tempDictionary["solarpanel"] = "not charging"
    
    #Channel 2
    temperatureValue = ReadChannel(temperature) #Celcius
    tempDictionary["temperature"] = 30 #temperatureValue
    '''
    if temperatureValue >= thresholdTemperatureUpper:
        if batteryActualVoltage >= thresholdvoltagelower:
            #Turn on exhaust
            pass
        else:
            #Don't turn on exhaust
            pass
    else:
        #Do Nothing
        pass
    '''
    #Debug Output
    #print("---------------------------------------------------------")
    #print("Battery Voltage: {} ({}V)".format(batteryReducedVoltage, batteryActualVoltage))
    #print("---------------------------------------------------------")
    #print("Solar Panel Voltage: {} ({}V)".format(solarPanelReducedVoltage, solarPanelActualVoltage))
    #print("---------------------------------------------------------")
    #print("Temperature: {}C ({}F)".format(temperatureValue, CelciusToFahrenheit(temperatureValue)))
    print("Done reading from sensors...")
    return tempDictionary;