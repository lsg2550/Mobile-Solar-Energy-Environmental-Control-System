#import
import os
from xml.etree import ElementTree

#Get File
fileName = 'status.xml'
filePath = os.path.abspath(fileName)

#Parse XML
parsedXML = ElementTree.parse(filePath)


log = parsedXML.findall('log') #Time & Date Log was Created
temp = parsedXML.findall('temperature') #Recorded Temperature
batt = parsedXML.find('battery') #Battery Voltage
exh = parsedXML.find('exhaust') #Exhaust On/Off
solar = parsedXML.find('solarpanel') #Is the Solar Panel Charging

for l in log:
	timeAndDate = l.find('time').text + ", " + l.find('date').text

for t in temp:
	print("Temperature as of ( {} ): {}".format(timeAndDate,t.find('celsius').text))
	print("Temperature as of ( {} ): {}".format(timeAndDate,t.find('fahrenheit').text))
	
print("Battery: {}.".format(batt.text))
print("Exhaust is {}.".format(exh.text))
print("Solar Panel is currently {}.".format(solar.text))