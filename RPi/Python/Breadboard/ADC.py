import spidev
import time
import os
 
spi = spidev.SpiDev()
spi.open(0,0)
 
def ReadChannel(channel):
	adc = spi.xfer2([1,(8+channel)<<4,0])
	data = ((adc[1]&3) << 8) + adc[2]
	return data
 
def ConvertVolts(data,place):
	volts = (data * 3.3) / float(1023) #TODO: CHANGE THIS
	volts = round(volts,place)
	return volts
 
battery = 0 
delay = 5
 
while True:
	batteryValue = ReadChannel(battery)
	batteryVolts = ConvertVolts(batteryValue, 2)
 
	print("--------------------------------------")
	print("Battery Voltage : {} ({}V)".format(batteryValue,batteryVolts))
 
	time.sleep(delay)