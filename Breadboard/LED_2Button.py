#Import Pkgs
import RPi.GPIO as GPIO
import time

#Init Variables
GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)
blinkCount = 2
count = 0

#Init Pin Locations
redLED = 18
greenLED = 17
button = 19

#Setup Inputs
GPIO.setup(redLED, GPIO.OUT)
GPIO.setup(greenLED, GPIO.OUT)
GPIO.setup(button, GPIO.IN, pull_up_down = GPIO.PUD_UP)

try:
	while count < blinkCount:
		print("Press the Button")
		buttonPress = GPIO.input(button)
		if buttonPress == False and count == 0:
			GPIO.output(redLED, GPIO.HIGH)
			print("Red LED is ON")
			time.sleep(3)
			GPIO.output(redLED, GPIO.LOW)
			print("Red LED is OFF")
			count += 1
		elif buttonPress == False and count == 1:
			GPIO.output(greenLED, GPIO.HIGH)
			print("Green LED is ON")
			time.sleep(3)
			GPIO.output(greenLED, GPIO.LOW)
			print("Green LED is OFF")
			count += 1
		time.sleep(0.5)
finally:
	GPIO.cleanup() #Reset Pins
