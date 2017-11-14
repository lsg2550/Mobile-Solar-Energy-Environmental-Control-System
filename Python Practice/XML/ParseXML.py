#import
import os
from xml.etree import ElementTree

#Get File
fileName = 'status.xml'
filePath = os.path.abspath(fileName)
#filePath = os.path.abspath(os.path.join('data', fileName))
#print(filePath) #Debug

#Parse XML
parsedXML = ElementTree.parse(filePath)
#print(parsedXML) #Debug

log = parsedXML.findall('log') #When the log XML was created
temp = parsedXML.findall('temperature') #What the temperature is

for l in log:
	timeAndDate = l.find('time').text + ", " + l.find('date').text
	
for t in temp:
	print("Temperature as of ( {} ): {}".format(timeAndDate,t.find('celsius').text))
	print("Temperature as of ( {} ): {}".format(timeAndDate,t.find('fahrenheit').text))