#Import
import RPi.GPIO as GPIO
import time

#Init
GPIO.setmode(GPIO.BCM)
GPIO.setup(19, GPIO.IN, pull_up_down = GPIO.PUD_UP)

#Do
try:
	while True:
		isButtonPressed = GPIO.input(19)
		if isButtonPressed == False:
			print ("Button is Pressed!")
			time.sleep(0.2)
		time.sleep(0.1)
except KeyboardInterrupt:
	GPIO.cleanup() #Cleanup
