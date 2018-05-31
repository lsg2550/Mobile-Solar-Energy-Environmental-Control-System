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

#Init
delay = 1.0
spi = spidev.SpiDev()
spi.open(0, 0)

#Analog Devices -- Analog Device = Channel
battery = 0

#Read from Analog2Digital Converter
while True:
    batteryReducedVoltage = ReadChannel(battery)
    batteryActualVoltage = ConvertVolts(batteryReducedVoltage, 2)
    
    print("---------------------------------------------------------")
    print("Battery Voltage: {} ({}V)".format(batteryReducedVoltage, batteryActualVoltage))
    
    time.sleep(delay)