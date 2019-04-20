# import
from notify_server import notification_for_thresholds
from multiprocessing import Process
import RPi.GPIO as GPIO
import Adafruit_DHT
import spidev
import serial

# Initialize
DHT11_SENSOR = Adafruit_DHT.DHT11
GPS_COORD_INACCESSIBLE = 1
GPS_NO_ERROR = 0

# Analog Devices = Channel #
SPI = spidev.SpiDev()
SPI.open(0, 0)
CHANNEL_V_BATT = 1 # ESU - Voltage
CHANNEL_V_PV = 2 # Solar Panel - Voltage
CHANNEL_C_BATT = 3 # ESU - Current
CHANNEL_C_PV = 4 # Solar Panel - Current
CHANNEL_C_CC = 5 # Charge Controller - Current

# GPIO Devices = GPIO Pin #
GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)
GPIO.cleanup()
DHT11_I = 23
DHT11_O = 24
EXHAUST = 4
GPIO.setup(EXHAUST, GPIO.OUT)

# Onboard Parts
VOLTAGE_REFERENCE = 2.5

# Battery Voltage Divider
VOLTAGE_BATT_RESISTOR_FROM_GND = 4.6
VOLTAGE_BATT_RESISTOR_FROM_POS = 45.0
VOLTAGE_BATT_DROP = VOLTAGE_BATT_RESISTOR_FROM_GND / (VOLTAGE_BATT_RESISTOR_FROM_GND + VOLTAGE_BATT_RESISTOR_FROM_POS)

# Solar Panel Voltage Divider
VOLTAGE_PV_RESISTOR_FROM_GND = 4.6
VOLTAGE_PV_RESISTOR_FROM_POS = 45.0
VOLTAGE_PV_DROP = VOLTAGE_PV_RESISTOR_FROM_GND / (VOLTAGE_PV_RESISTOR_FROM_GND + VOLTAGE_PV_RESISTOR_FROM_POS)

# Shunt #1 OpAmp
SHUNT_ONE_OPAMP_RESISTOR_FEEDBACK = 44.9
SHUNT_ONE_OPAMP_RESISTOR_ONE = 0.994
SHUNT_ONE_GAIN = 1 + (SHUNT_ONE_OPAMP_RESISTOR_FEEDBACK / SHUNT_ONE_OPAMP_RESISTOR_ONE)

# Shunt #2 OpAmp
SHUNT_TWO_OPAMP_RESISTOR_FEEDBACK = 45.0
SHUNT_TWO_OPAMP_RESISTOR_ONE = 0.998
SHUNT_TWO_GAIN = 1 + (SHUNT_TWO_OPAMP_RESISTOR_FEEDBACK / SHUNT_TWO_OPAMP_RESISTOR_ONE)

# Shunt #3 OpAmp
SHUNT_THREE_OPAMP_RESISTOR_FEEDBACK = 45.0
SHUNT_THREE_OPAMP_RESISTOR_ONE = 0.994
SHUNT_THREE_GAIN = 1 + (SHUNT_THREE_OPAMP_RESISTOR_FEEDBACK / SHUNT_THREE_OPAMP_RESISTOR_ONE)

# Shunt Resistance
SHUNT_RESISTANCE = 0.0075

# Serial Devices
try: SERIAL_GPS = serial.Serial(port="/dev/ttyACM0", baudrate=9600, timeout=1)
except Exception as e: print(e)

def convert_digital_to_analog_divider(data, v_ref, v_drop, decimal_place): 
    '''Used by Voltage Divider'''
    volts = (data / float(1023)) * (v_ref/v_drop)
    volts = round(volts, decimal_place)
    return volts

def convert_digital_to_analog_shunt(data, v_ref, shunt_gain, decimal_place):
    '''Used by Shunts'''
    volts = (data / float(1023)) * (v_ref)
    volts = round(volts/shunt_gain, decimal_place)
    return volts

def celcius_to_fahrenheit(temperature_celcius): return ((temperature_celcius * 9/5) + 32)

def fahrenheit_to_celcius(temperature_fahrenheit): return ((temperature_fahrenheit - 32) * 5/9)

def read_from_adc_channel(channel):
    '''ADC will read from a given channel and convert its value from analog to digital'''
    analog_to_digital_channel_read = SPI.xfer2([1, (8 + channel) << 4, 0])
    analog_to_digital_channel_data = ((analog_to_digital_channel_read[1] & 3) << 8) + analog_to_digital_channel_read[2]
    #print("Channel {} reads a digital value of {}".format(channel, analog_to_digital_channel_data))    
    return analog_to_digital_channel_data
# ReadADCChannel end

def read_from_gps():
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

def read_from_sensors(threshold_battery_voltage_lower=None, threshold_battery_voltage_upper=None,
                      threshold_battery_current_lower=None, threshold_battery_current_upper=None,
                      threshold_solar_panel_voltage_lower=None, threshold_solar_panel_voltage_upper=None,
                      threshold_solar_panel_current_lower=None, threshold_solar_panel_current_upper=None,
                      threshold_charge_controller_current_lower=None, threshold_charge_controller_current_upper=None,
                      threshold_temperature_inner_lower=None, threshold_temperature_inner_upper=None,
                      threshold_temperature_outer_lower=None, threshold_temperature_outer_upper=None,
                      threshold_humidity_inner_lower=None, threshold_humidity_inner_upper=None,
                      threshold_humidity_outer_lower=None, threshold_humidity_outer_upper=None):
    # Init - Global Var 
    global PROCESS_NOTIFICATION
    
    # Battery Thresholds
    threshold_bvl = float(threshold_battery_voltage_lower)
    threshold_bvu = float(threshold_battery_voltage_upper)
    threshold_bcl = float(threshold_battery_current_lower)
    threshold_bcu = float(threshold_battery_current_upper)
    # Solar Panel Thresholds
    threshold_spvl = float(threshold_solar_panel_voltage_lower)
    threshold_spvu = float(threshold_solar_panel_voltage_upper)
    threshold_spcl = float(threshold_solar_panel_current_lower)
    threshold_spcu = float(threshold_solar_panel_current_upper)
    # Charge Controller Thresholds
    threshold_cccl = float(threshold_charge_controller_current_lower)
    threshold_cccu = float(threshold_charge_controller_current_upper)
    # Temperature Thresholds
    threshold_til = float(threshold_temperature_inner_lower)
    threshold_tiu = float(threshold_temperature_inner_upper)
    threshold_tol = float(threshold_temperature_outer_lower)
    threshold_tou = float(threshold_temperature_outer_upper)
    # Humidity Thresholds
    threshold_hil = float(threshold_humidity_inner_lower)
    threshold_hiu = float(threshold_humidity_inner_upper)
    threshold_hol = float(threshold_humidity_outer_lower)
    threshold_hou = float(threshold_humidity_outer_upper)
    
    # Dictionary to hold {Sensor => Value}
    temporary_sensor_dictionary = {}

    # Read from Shunts - ADC Channels 3 & 4 & 5
    v1 = convert_digital_to_analog_shunt(read_from_adc_channel(CHANNEL_C_BATT), VOLTAGE_REFERENCE, SHUNT_ONE_GAIN, 5) # From Shunt #1 OpAmp
    v2 = convert_digital_to_analog_shunt(read_from_adc_channel(CHANNEL_C_CC), VOLTAGE_REFERENCE, SHUNT_TWO_GAIN, 5) # From Shunt #3 OpAmp
    v4 = convert_digital_to_analog_shunt(read_from_adc_channel(CHANNEL_C_PV), VOLTAGE_REFERENCE, SHUNT_THREE_GAIN, 5) # From Shunt #2 OpAmp

    # Read Battery & Solar Panel Voltage - ADC Channels 1 & 2 - then calculate Battery, Solar Panel, and Charge Controller Current
    battery_voltage_value = convert_digital_to_analog_divider(read_from_adc_channel(CHANNEL_V_BATT), VOLTAGE_REFERENCE, VOLTAGE_BATT_DROP, 2) - v1 # From Voltage Divider #1
    solar_panel_voltage_value = convert_digital_to_analog_divider(read_from_adc_channel(CHANNEL_V_PV), VOLTAGE_REFERENCE, VOLTAGE_PV_DROP, 2) # From Voltage Divider #2
    battery_current_value = (v2 - v1) / SHUNT_RESISTANCE
    solar_panel_current_value = v4 / SHUNT_RESISTANCE
    charge_controller_current_value = (v1 - v4) / SHUNT_RESISTANCE
    
    # Debug
    print("Voltage Read from Shunts: V1:{}V, V2:{}V, V3:{}V".format(v1, v2, v4))
    print("Battery Voltage:{}V, Battery Current:{}A, PV Voltage:{}V, PV Current:{}A, Charge Controller Current:{}A".format(battery_voltage_value, battery_current_value, solar_panel_voltage_value, solar_panel_current_value, charge_controller_current_value))
    
    # Inner and Outer Temperature Sensors
    humidity_inner, temperature_inner = Adafruit_DHT.read(DHT11_SENSOR, DHT11_I)
    humidity_outer, temperature_outer = Adafruit_DHT.read(DHT11_SENSOR, DHT11_O)

    # GPS
    try: gps_latitude_longitude = read_from_gps()
    except Exception: gps_latitude_longitude = [GPS_COORD_INACCESSIBLE, 0, 0]

    # Check for notification purposes
    PROCESS_NOTIFICATION = Process(target=notification_for_thresholds, args=(battery_voltage_value, battery_current_value, solar_panel_voltage_value, solar_panel_current_value, charge_controller_current_value, temperature_inner, temperature_outer, humidity_inner, humidity_outer, threshold_bvl, threshold_bvu, threshold_bcl, threshold_bcu, threshold_spvl, threshold_spvu, threshold_spcl, threshold_spcu, threshold_cccl, threshold_cccu, threshold_til, threshold_tiu, threshold_tol, threshold_tou, threshold_hil, threshold_hiu, threshold_hol, threshold_hou), daemon=True)
    PROCESS_NOTIFICATION.start()

    # Exhaust Operations
    try:
        if temperature_inner >= threshold_tiu:  # For Hot Air -> Cold Air
            if battery_voltage_value > threshold_bvl:
                temporary_sensor_dictionary["exhaust"] = "on"
                GPIO.output(EXHAUST, GPIO.HIGH)
            else:
                temporary_sensor_dictionary["exhaust"] = "off"
                GPIO.output(EXHAUST, GPIO.LOW)
    except Exception: # Means that temperature_inner did not receive a value from the sensor, so just disable exhaust
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
    if gps_latitude_longitude[0] == GPS_NO_ERROR: temporary_sensor_dictionary["gps"] = [gps_latitude_longitude[1], gps_latitude_longitude[2]]
    else: temporary_sensor_dictionary["gps"] = ["NULL", "NULL"]

    # Return
    return temporary_sensor_dictionary