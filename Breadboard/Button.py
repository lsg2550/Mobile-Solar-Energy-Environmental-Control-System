#Import
import RPi.GPIO as GPIO
import time

#Init
GPIO.setmode(GPIO.BCM)
GPIO.setup(21, GPIO.IN, pull_up_down = GPIO.PUD_Up)

#Do
try:
	while True:
		isButtonPressed = GPIO.input(21)
		if isButtonPressed != False:
			print ("Button is Pressed!")
			time.sleep(0.2)
		time.sleep(0.1)
except KeyboardInterrupt:
	GPIO.cleanup() #Cleanup
