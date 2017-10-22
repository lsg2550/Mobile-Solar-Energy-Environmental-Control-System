import RPi.GPIO as GPIO
import time

#Setup
GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)

#Pins to Use
GPIO.setup(17, GPIO.OUT)
GPIO.setup(18, GPIO.OUT)

#Turn LEDs On
for x in range(0,2):
	if x == 0:
		print "Green LED is On!"
		GPIO.output(17, GPIO.HIGH) #Turn 17 On
	elif x == 1:
		print "Red LED is On!"
		GPIO.output(18, GPIO.HIGH) #Turn 18 On
	time.sleep(2.5)

print "Sleeping for 5 seconds..."
time.sleep(5)

for x in range(0,2):
	if x == 0:
		print "Green LED is Off!"
		GPIO.output(17, GPIO.LOW) #Turn 17 Off
	elif x == 1:
		print "Red LED is Off!"
		GPIO.output(18, GPIO.LOW) #Turn 18 Off
	time.sleep(2.5)
