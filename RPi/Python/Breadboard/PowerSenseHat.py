#Import
import RPi.GPIO as GPIO
from sense_hat import SenseHat;
import time

#Init
try:
    GPIO.setmode(GPIO.BCM)
    GPIO.setwarnings(False)
    GPIO.setup(1, GPIO.OUT)
    GPIO.setup(2, GPIO.OUT)
    GPIO.output(1, GPIO.HIGH)
    GPIO.output(2, GPIO.HIGH)
    
    GPIO.setup(3, GPIO.OUT)
    GPIO.setup(23, GPIO.OUT)
    GPIO.setup(24, GPIO.OUT)
    GPIO.setup(25, GPIO.OUT)
    GPIO.setup(8, GPIO.OUT)
    GPIO.output(3, GPIO.HIGH)
    GPIO.output(23, GPIO.HIGH)
    GPIO.output(24, GPIO.HIGH)
    GPIO.output(25, GPIO.HIGH)
    GPIO.output(8, GPIO.HIGH)
    
    r = input("Please Hold")
finally:
    GPIO.cleanup()