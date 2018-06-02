#import
import spidev
import time
import os

#Init
delay = 1.0
spi = spidev.SpiDev()
spi.open(0, 0)

#Analog Devices -- Analog Device = Channel
battery = 0 #car battery
solar_panel = 1 #solar panel
temperature = 2 #temperature sensor
exhaust = 3 #Note: might delete this - as the exhaust might be handled by the cobbler

def ReadChannel(channel): #Reads from given channel
    adc = spi.xfer2([1, (8 + channel) << 4,0])
    data = ((adc[1] & 3) << 8) + adc[2]
    return data

def ConvertVolts(data, place): #Converts from read voltage to actual voltage (due to resisters
    volts = (data * 3.3) / float(1023) #TODO: Change this
    volts = round(volts, place)
    return volts

def CelciusToFahrenheit(temperatureCelcius):
    return ((temperatureCelcius * 9/5) + 32)

def FahrenheitToCelcius(temperatureFahrenheit):
    return ((temperatureFahrenheit - 32) * 5/9)

def ReadFromSensors():
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
    if solarPanelActualVoltage >= 1:
        tempDictionary["solarpanel"] = "charging"
    else:
        tempDictionary["solarpanel"] = "not charging"
    
    #Channel 2
    #temperatureValue = ReadChannel(temperature)
    #tempDictionary["temperature"] = temperatureValue
    
    #Debug Output
    #print("---------------------------------------------------------")
    #print("Battery Voltage: {} ({}V)".format(batteryReducedVoltage, batteryActualVoltage))
    #print("---------------------------------------------------------")
    #print("Solar Panel Voltage: {} ({}V)".format(solarPanelReducedVoltage, solarPanelActualVoltage))
    #print("---------------------------------------------------------")
    #print("Temperature: {}C ({}F)".format(temperatureValue, CelciusToFahrenheit(temperatureValue)))
    #print("")
    return tempDictionary;