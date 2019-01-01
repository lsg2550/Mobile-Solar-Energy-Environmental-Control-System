# import
from datetime import datetime
from threading import Thread
import RPi.GPIO as GPIO
import Adafruit_DHT
import requests
import spidev
import serial
import time
import os
import random

# RaspberryPi Identification Number (rpid) & Payload for Server Confirmation
rpid = 0
pipayload = {"rpid": rpid}

# Initialize
NOTIFICATION_THREAD = None
GPS_NO_ERROR = 0
GPS_COORD_INACCESSIBLE = 1
DHT11_SENSOR = Adafruit_DHT.DHT11

# Analog Devices = Channel #
spi = spidev.SpiDev()
spi.open(0, 0)
battery_voltage = 0
battery_current = 1
#charge_controller_voltage = 2
#charge_controller_current = 3
#solar_panel_voltage = 4
#solar_panel_current = 5

# GPIO Devices = GPIO Pin #
GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)
GPIO.cleanup()
DHT11_I = 22
DHT11_O = 17
EXHAUST = 4
GPIO.setup(EXHAUST, GPIO.OUT)

# Previous Temperature/Humidity/GPS Values
previous_temperature_value_inner = 0
previous_humidity_value_inner = 0
previous_temperature_value_outer = 0
previous_humidity_value_outer = 0
previous_latitude = 0
previous_longitude = 0

# Serial Devices
try: SERIAL_GPS = serial.Serial(port = "/dev/ttyACM0", baudrate = 9600, timeout = 1)
except Exception as e: print(e)

def ReadGPS():
    # Init
    global GPS_NO_ERROR
    global GPS_COORD_INACCESSIBLE
    timeout_max_count = 100
    timeout_counter = 0
    gps_status_latitude_longitude = [None, None, None]
    
    while True:
        serial_line_read = SERIAL_GPS.readline().decode("utf-8")
        gps_data_read = serial_line_read.split(",")
        if gps_data_read[0] == "$GPRMC" and gps_data_read[2] == "A": 
            # Latitude
            latitude_gps = float(gps_data_read[3]) if gps_data_read[4] != "S" else -float(gps_data_read[3])
            latitude_degree = int(latitude_gps/100)
            latitude_minute = latitude_gps - latitude_degree*100
            latitude_actual = latitude_degree + (latitude_minute/60)

            # Longitude
            longitude_gps = float(gps_data_read[5]) if gps_data_read[6] != "W" else -float(gps_data_read[5])
            longitude_degree = int(longitude_gps/100)
            longitude_minute = longitude_gps - longitude_degree*100
            longitude_actual = longitude_degree + (longitude_minute/60)

            gps_status_latitude_longitude[0] = GPS_NO_ERROR
            gps_status_latitude_longitude[1] = latitude_actual
            gps_status_latitude_longitude[2] = longitude_actual
            break
        # Test for Timeout - May be caused by the GPS module not being able to detect its location   
        timeout_counter += 1
        if timeout_counter > timeout_max_count:
            gps_status_latitude_longitude[0] = GPS_COORD_INACCESSIBLE
            gps_status_latitude_longitude[1] = latitude_actual
            gps_status_latitude_longitude[2] = longitude_actual
            break
    return gps_status_latitude_longitude
# ReadGPS end

def ReadADCChannel(channel):
    analog_to_digital_channel_read = spi.xfer2([1, (8 + channel) << 4, 0])
    analog_to_digital_channel_data = ((analog_to_digital_channel_read[1] & 3) << 8) + analog_to_digital_channel_read[2]
    return analog_to_digital_channel_data
# ReadADCChannel end

def CheckAndNotify(battery_voltage_value, battery_current_value,
                   solar_panel_voltage_value, solar_panel_current_value,
                   charge_controller_voltage_value, charge_controller_current_value,
                   temperature_inner_value, temperature_outer_value,
                   threshold_battery_voltage_lower, threshold_battery_voltage_upper,
                   threshold_battery_current_lower, threshold_battery_current_upper,
                   threshold_solar_panel_voltage_lower, threshold_solar_panel_voltage_upper,
                   threshold_solar_panel_current_lower, threshold_solar_panel_current_upper,
                   threshold_charge_controller_voltage_lower, threshold_charge_controller_voltage_upper,
                   threshold_charge_controller_current_lower, threshold_charge_controller_current_upper,
                   threshold_temperature_inner_lower, threshold_temperature_inner_upper,
                   threshold_temperature_outer_lower, threshold_temperature_outer_upper):
    try:
        current_hour = int(datetime.now().strftime("%H")) # Uses military hours (0-23)
        if current_hour >= 9 and current_hour <= 16:
            if battery_voltage_value <= threshold_battery_voltage_lower or battery_voltage_value >= threshold_battery_voltage_upper:
                pipayload["noti"] = "bvoltage"
                server_confirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=pipayload)
                # print(serverConfirmation.text.strip())
                pipayload.pop("noti")
            if battery_current_value <= threshold_battery_current_lower or battery_current_value >= threshold_battery_current_upper:
                pipayload["noti"] = "bcurrent"
                server_confirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=pipayload)
                # print(serverConfirmation.text.strip())
                pipayload.pop("noti")
            if solar_panel_voltage_value <= threshold_solar_panel_voltage_lower or solar_panel_voltage_value >= threshold_solar_panel_voltage_upper:
                pipayload["noti"] = "spvoltage"
                server_confirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=pipayload)
                # print(serverConfirmation.text.strip())
                pipayload.pop("noti")
            if solar_panel_current_value <= threshold_solar_panel_current_lower or solar_panel_current_value >= threshold_solar_panel_current_upper:
                pipayload["noti"] = "spcurrent"
                server_confirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=pipayload)
                # print(serverConfirmation.text.strip())
                pipayload.pop("noti")
            if charge_controller_voltage_value <= threshold_charge_controller_voltage_lower or charge_controller_voltage_value >= threshold_charge_controller_voltage_upper:
                pipayload["noti"] = "ccvoltage"
                server_confirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=pipayload)
                # print(serverConfirmation.text.strip())
                pipayload.pop("noti")
            if charge_controller_current_value <= threshold_charge_controller_current_lower or charge_controller_current_value >= threshold_charge_controller_current_upper:
                pipayload["noti"] = "cccurrent"
                server_confirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=pipayload)
                # print(serverConfirmation.text.strip())
                pipayload.pop("noti")
                
            if temperature_inner_value is not None:
                if temperature_inner_value <= threshold_temperature_inner_lower or temperature_inner_value >= threshold_temperature_inner_upper:
                    pipayload["noti"] = "temperatureI"
                    server_confirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=pipayload)
                    # print(serverConfirmation.text.strip())
                    pipayload.pop("noti")
            if temperature_outer_value is not None:                
                if temperature_outer_value <= threshold_temperature_outer_lower or temperature_outer_value >= threshold_temperature_outer_upper: 
                    pipayload["noti"] = "temperatureO"
                    server_confirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pinotification.php", params=pipayload)
                    # print(serverConfirmation.text.strip())
                    pipayload.pop("noti")
    except Exception as e: print(e)  # Unable to connect to internet, so just disregard sending a notification
# CheckAndNotify end

def ConvertVolts(data, place): return round((data * 3.3) / float(1023))
def CelciusToFahrenheit(temperature_celcius): return ((temperature_celcius * 9/5) + 32)
def FahrenheitToCelcius(temperature_fahrenheit): return ((temperature_fahrenheit - 32) * 5/9)

def ReadFromSensors(threshold_battery_voltage_lower=None, threshold_battery_voltage_upper=None,
                    threshold_battery_current_lower=None, threshold_battery_current_upper=None,
                    threshold_solar_panel_voltage_lower=None, threshold_solar_panel_voltage_upper=None,
                    threshold_solar_panel_current_lower=None, threshold_solar_panel_current_upper=None,
                    threshold_charge_controller_voltage_lower=None, threshold_charge_controller_voltage_upper=None,
                    threshold_charge_controller_current_lower=None, threshold_charge_controller_current_upper=None,
                    threshold_temperature_inner_lower=None, threshold_temperature_inner_upper=None,
                    threshold_temperature_outer_lower=None, threshold_temperature_outer_upper=None):
    # Init - Global Var 
    global NOTIFICATION_THREAD
    global DHT11_SENSOR
    global GPS_NO_ERROR
    global GPS_COORD_INACCESSIBLE
    global previous_temperature_value_inner
    global previous_humidity_value_inner
    global previous_temperature_value_outer
    global previous_humidity_value_outer
    global previous_latitude
    global previous_longitude
    
    # Thresholds
    # Battery Thresholds
    thresholdBVL = float(threshold_battery_voltage_lower)
    thresholdBVU = float(threshold_battery_voltage_upper)
    thresholdBCL = float(threshold_battery_current_lower)
    thresholdBCU = float(threshold_battery_current_upper)
    # Solar Panel Thresholds
    thresholdSPVL = float(threshold_solar_panel_voltage_lower)
    thresholdSPVU = float(threshold_solar_panel_voltage_upper)
    thresholdSPCL = float(threshold_solar_panel_current_lower)
    thresholdSPCU = float(threshold_solar_panel_current_upper)
    # Charge Controller Thresholds
    thresholdCCVL = float(threshold_charge_controller_voltage_lower)
    thresholdCCVU = float(threshold_charge_controller_voltage_upper)
    thresholdCCCL = float(threshold_charge_controller_current_lower)
    thresholdCCCU = float(threshold_charge_controller_current_upper)
    # Temperature Thresolds
    thresholdTIL = float(threshold_temperature_inner_lower)
    thresholdTIU = float(threshold_temperature_inner_upper)
    thresholdTOL = float(threshold_temperature_outer_lower)
    thresholdTOU = float(threshold_temperature_outer_upper)
    
    # Dictionary to hold {Sensor => Value}
    temporary_sensor_dictionary = {}

    # ADC Channel 0 and 1 - Battery
    battery_voltage_value = ReadADCChannel(battery_voltage) # From Resistor Network - Circuit Diagram
    battery_current_value = ReadADCChannel(battery_current) # From OpAmp

    # ADC Channel 2 and 3 - Charge Controller
    charge_controller_voltage_value = random.randint(0, 20)
    charge_controller_current_value = random.randint(0, 10000)
    
    # ADC Channel 4 and 5 - Solar Panel
    solar_panel_voltage_value = random.randint(0, 17)
    solar_panel_current_value = random.randint(0, 1000)

    # Inner and Outer Temperature Sensors
    humidity_inner, temperature_inner = Adafruit_DHT.read(DHT11_SENSOR, DHT11_I)
    humidity_outer, temperature_outer = Adafruit_DHT.read(DHT11_SENSOR, DHT11_O)
    if temperature_inner is not None: previous_temperature_value_inner = temperature_inner
    if humidity_inner is not None: previous_humidity_value_inner = humidity_inner
    if temperature_outer is not None: previous_temperature_value_outer = temperature_outer
    if humidity_outer is not None: previous_humidity_value_outer = humidity_outer

    # GPS
    try: gps_latitude_longitude = ReadGPS()
    except Exception as e: gps_latitude_longitude = [GPS_COORD_INACCESSIBLE, 0, 0]

    # Check for notification purposes
    if NOTIFICATION_THREAD == None or not NOTIFICATION_THREAD.isAlive():
        NOTIFICATION_THREAD = Thread(target=CheckAndNotify, args=(battery_voltage_value, battery_current_value, solar_panel_voltage_value, solar_panel_current_value, charge_controller_voltage_value, charge_controller_current_value, temperature_inner, temperature_outer, thresholdBVL, thresholdBVU, thresholdBCL, thresholdBCU, thresholdSPVL, thresholdSPVU, thresholdSPCL, thresholdSPCU, thresholdCCVL, thresholdCCVU, thresholdCCCL, thresholdCCCU, thresholdTIL, thresholdTIU, thresholdTOL, thresholdTOU, ))
        NOTIFICATION_THREAD.start()

    # ESSO Operations
    # Solar Panel Operations
    if battery_voltage_value >= thresholdBVU: temporary_sensor_dictionary["solarpanel"] = "not charging"
    elif battery_voltage_value <= thresholdBVU: temporary_sensor_dictionary["solarpanel"] = "charging"
    
    try: # Exhaust Operations
        if temperature_inner >= thresholdTIU:  # For Hot Air -> Cold Air
            if battery_voltage_value > thresholdBVL:
                temporary_sensor_dictionary["exhaust"] = "on"
                GPIO.output(EXHAUST, GPIO.HIGH)
            else:
                temporary_sensor_dictionary["exhaust"] = "off"
                GPIO.output(EXHAUST, GPIO.LOW)
    except Exception as e:
        if previous_temperature_value_inner >= thresholdTIU:  # For Hot Air -> Cold Air
            if battery_voltage_value > thresholdBVL:
                temporary_sensor_dictionary["exhaust"] = "on"
                GPIO.output(EXHAUST, GPIO.HIGH)
            else:
                temporary_sensor_dictionary["exhaust"] = "off"
                GPIO.output(EXHAUST, GPIO.LOW)
            
    # Populate tempDictionary with recorded values
    # Battery Values
    temporary_sensor_dictionary["batteryvoltage"] = battery_voltage_value
    temporary_sensor_dictionary["batterycurrent"] = battery_current_value
    # Solar Panel Values
    temporary_sensor_dictionary["solarpanelvoltage"] = solar_panel_voltage_value
    temporary_sensor_dictionary["solarpanelcurrent"] = solar_panel_current_value
    # Charge Controller Values
    temporary_sensor_dictionary["chargecontrollervoltage"] = charge_controller_voltage_value
    temporary_sensor_dictionary["chargecontrollercurrent"] = charge_controller_current_value
    
    # Temperature Values
    if temperature_inner is None: temporary_sensor_dictionary["temperatureinner"] = "NULL"
    else: temporary_sensor_dictionary["temperatureinner"] = temperature_inner
    if humidity_inner is None: temporary_sensor_dictionary["humidityinner"] = "NULL"
    else: temporary_sensor_dictionary["humidityinner"] = humidity_inner
    if temperature_outer is None: temporary_sensor_dictionary["temperatureouter"] = "NULL"
    else: temporary_sensor_dictionary["temperatureouter"] = temperature_outer
    if humidity_outer is None: temporary_sensor_dictionary["humidityouter"] = "NULL"
    else: temporary_sensor_dictionary["humidityouter"] = humidity_outer
        
    # GPS Values
    if gps_latitude_longitude[0] == GPS_NO_ERROR:
        temporary_sensor_dictionary["gps"] = [gps_latitude_longitude[1]]
        temporary_sensor_dictionary["gps"].append(gps_latitude_longitude[2])
        previous_latitude = gps_latitude_longitude[1]
        previous_longitude = gps_latitude_longitude[2]
    else:
        temporary_sensor_dictionary["gps"] = [previous_latitude]
        temporary_sensor_dictionary["gps"].append(previous_longitude)

    # Return
    return temporary_sensor_dictionary