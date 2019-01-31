# import
from notify_server import CheckAndNotify
from datetime import datetime
from threading import Thread
import RPi.GPIO as GPIO
import math_calc as MC
import Adafruit_DHT
import spidev
import serial
import time
import os

# Initialize
NOTIFICATION_THREAD = None
GPS_NO_ERROR = 0
GPS_COORD_INACCESSIBLE = 1
DHT11_SENSOR = Adafruit_DHT.DHT11

# Analog Devices = Channel #
spi = spidev.SpiDev()
spi.open(0, 0)
battery_voltage = 1 # ESU - Voltage
solar_voltage = 2 # Solar Panel - Voltage
battery_current = 3 # ESU - Current
solar_current = 4 # Solar Panel - Current
charge_con_current = 5 # Charge Controller - Current

# GPIO Devices = GPIO Pin #
GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)
GPIO.cleanup()
DHT11_I = 22
DHT11_O = 17
EXHAUST = 4
GPIO.setup(EXHAUST, GPIO.OUT)

# Onboard Parts
actual_five_voltage_rail = 5.30

# Battery Voltage Divider
voltage_divider_batt_resistor_from_ground = 2.35
voltage_divider_batt_resistor_from_positive = 9.93
voltage_divider_batt_drop = voltage_divider_batt_resistor_from_ground / (voltage_divider_batt_resistor_from_ground + voltage_divider_batt_resistor_from_positive)

# Solar Panel Voltage Divider
voltage_divider_pv_resistor_from_ground = 2.35
voltage_divider_pv_resistor_from_positive = 9.93
voltage_divider_pv_drop = voltage_divider_pv_resistor_from_ground / (voltage_divider_pv_resistor_from_ground + voltage_divider_pv_resistor_from_positive)

# Shunt #1 OpAmp
shunt_one_opamp_resistor_feedback = 44.9
shunt_one_opamp_resistor_one = 0.998
shunt_one_gain = 1 + (shunt_one_opamp_resistor_feedback / shunt_one_opamp_resistor_one)

# Shunt #2 OpAmp
shunt_two_opamp_resistor_feedback = 45
shunt_two_opamp_resistor_one = 0.994
shunt_two_gain = 1 + (shunt_two_opamp_resistor_feedback / shunt_two_opamp_resistor_one)

# Shunt #3 OpAmp
shunt_three_opamp_resistor_feedback = 45
shunt_three_opamp_resistor_one = 0.994
shunt_three_gain = 1 + (shunt_three_opamp_resistor_feedback / shunt_three_opamp_resistor_one)

# Serial Devices
try: SERIAL_GPS = serial.Serial(port = "/dev/ttyACM0", baudrate = 9600, timeout = 1)
except Exception as e: print(e)

def ReadADCChannel(channel):
    analog_to_digital_channel_read = spi.xfer2([1, (8 + channel) << 4, 0])
    analog_to_digital_channel_data = ((analog_to_digital_channel_read[1] & 3) << 8) + analog_to_digital_channel_read[2]
    return analog_to_digital_channel_data
# ReadADCChannel end

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

def ReadFromSensors(threshold_battery_voltage_lower=None, threshold_battery_voltage_upper=None,
                    threshold_battery_current_lower=None, threshold_battery_current_upper=None,
                    threshold_solar_panel_voltage_lower=None, threshold_solar_panel_voltage_upper=None,
                    threshold_solar_panel_current_lower=None, threshold_solar_panel_current_upper=None,
                    threshold_charge_controller_current_lower=None, threshold_charge_controller_current_upper=None,
                    threshold_temperature_inner_lower=None, threshold_temperature_inner_upper=None,
                    threshold_temperature_outer_lower=None, threshold_temperature_outer_upper=None):
    # Init - Global Var 
    global NOTIFICATION_THREAD
    global DHT11_SENSOR
    global GPS_NO_ERROR
    global GPS_COORD_INACCESSIBLE
    
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
    thresholdCCCL = float(threshold_charge_controller_current_lower)
    thresholdCCCU = float(threshold_charge_controller_current_upper)
    # Temperature Thresolds
    thresholdTIL = float(threshold_temperature_inner_lower)
    thresholdTIU = float(threshold_temperature_inner_upper)
    thresholdTOL = float(threshold_temperature_outer_lower)
    thresholdTOU = float(threshold_temperature_outer_upper)
    
    # Dictionary to hold {Sensor => Value}
    temporary_sensor_dictionary = {}

    # Read Battery Voltage and Current - ADC CHannels 1 & 3
    battery_voltage_value = MC.ConvertVolts(ReadADCChannel(battery_voltage), actual_five_voltage_rail, voltage_divider_batt_drop, 2) # From Voltage Divider #1
    battery_current_value = MC.ConvertAmps(ReadADCChannel(battery_current), actual_five_voltage_rail, shunt_one_gain, 2) # From Shunt #1 OpAmp
    
    # Read Solar Panel Voltage and Current - ADC Channels 2 & 4
    solar_panel_voltage_value = MC.ConvertVolts(ReadADCChannel(solar_voltage), actual_five_voltage_rail, voltage_divider_pv_drop, 2) # From Voltage Divider #2
    solar_panel_current_value = MC.ConvertAmps(ReadADCChannel(solar_current), actual_five_voltage_rail, shunt_two_gain, 2) # From Shunt #2 OpAmp

    # Read Charge Controller Current - ADC Channels 5
    charge_controller_current_value = MC.ConvertAmps(ReadADCChannel(charge_con_current), actual_five_voltage_rail, shunt_three_gain, 2) # From Shunt #3 OpAmp

    # Inner and Outer Temperature Sensors
    humidity_inner, temperature_inner = Adafruit_DHT.read(DHT11_SENSOR, DHT11_I)
    humidity_outer, temperature_outer = Adafruit_DHT.read(DHT11_SENSOR, DHT11_O)

    # GPS
    try: gps_latitude_longitude = ReadGPS()
    except Exception as e: gps_latitude_longitude = [GPS_COORD_INACCESSIBLE, 0, 0]

    # Check for notification purposes
    if NOTIFICATION_THREAD == None or not NOTIFICATION_THREAD.isAlive():
        NOTIFICATION_THREAD = Thread(target=CheckAndNotify, args=(battery_voltage_value, battery_current_value, solar_panel_voltage_value, solar_panel_current_value, charge_controller_current_value, temperature_inner, temperature_outer, thresholdBVL, thresholdBVU, thresholdBCL, thresholdBCU, thresholdSPVL, thresholdSPVU, thresholdSPCL, thresholdSPCU, thresholdCCCL, thresholdCCCU, thresholdTIL, thresholdTIU, thresholdTOL, thresholdTOU, ))
        NOTIFICATION_THREAD.setDaemon(True)
        NOTIFICATION_THREAD.start()

    # Exhaust Operations
    try: 
        if temperature_inner >= thresholdTIU:  # For Hot Air -> Cold Air
            if battery_voltage_value > thresholdBVL:
                temporary_sensor_dictionary["exhaust"] = "on"
                GPIO.output(EXHAUST, GPIO.HIGH)
            else:
                temporary_sensor_dictionary["exhaust"] = "off"
                GPIO.output(EXHAUST, GPIO.LOW)
    except Exception as e:
        temporary_sensor_dictionary["exhaust"] = "off"
        GPIO.output(EXHAUST, GPIO.LOW)
            
    # Populate tempDictionary with recorded values
    temporary_sensor_dictionary["batteryvoltage"] = battery_voltage_value
    temporary_sensor_dictionary["batterycurrent"] = battery_current_value
    temporary_sensor_dictionary["solarpanelvoltage"] = solar_panel_voltage_value
    temporary_sensor_dictionary["solarpanelcurrent"] = solar_panel_current_value
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
    else:
        temporary_sensor_dictionary["gps"] = ["NULL"]
        temporary_sensor_dictionary["gps"].append("NULL")

    # Return
    return temporary_sensor_dictionary