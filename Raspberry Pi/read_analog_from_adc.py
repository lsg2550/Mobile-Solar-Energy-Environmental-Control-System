#import
import spidev
import time
import os

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

#Init
delay = 1.0
spi = spidev.SpiDev()
spi.open(0, 0)

#Analog Devices -- Analog Device = Channel
battery = 0 #car battery
solar_panel = 1 #solar panel
temperature = 2 #temperature sensor
exhaust = 3 #Note: might delete this - as the exhaust might be handled by the cobbler

#Read from Analog2Digital Converter
while True:
    #Channel 0
    batteryReducedVoltage = ReadChannel(battery)
    batteryActualVoltage = ConvertVolts(batteryReducedVoltage, 2)
    print("---------------------------------------------------------")
    print("Battery Voltage: {} ({}V)".format(batteryReducedVoltage, batteryActualVoltage))
    
    #Channel 1
    solarPanelReducedVoltage = ReadChannel(solar_panel)
    solarPanelActualVoltage = ConvertVolts(solarPanelReducedVoltage, 2)
    print("---------------------------------------------------------")
    print("Solar Panel Voltage: {} ({}V)".format(solarPanelReducedVoltage, solarPanelActualVoltage))
    
    #Channel 2
    #temperatureValue = ReadChannel(temperature)
    #print("---------------------------------------------------------")
    #print("Temperature: {}C ({}F)".format(temperatureValue, CelciusToFahrenheit(temperatureValue)))

    time.sleep(delay)