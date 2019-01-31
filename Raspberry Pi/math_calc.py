def ConvertVolts(data, actual_voltage, actual_drop, place): # Used by Voltage Divider
    volts = (data / float(1023)) * (actual_voltage/actual_drop)
    volts = round(volts, place)
    return volts

def ConvertAmps(data, actual_voltage, actual_gain, place): # Used by Operational Amplifers
    volts = (data / float(1023)) * (actual_voltage / actual_gain) * (100/0.75)
    volts = round(volts, place)
    return volts

def CelciusToFahrenheit(temperature_celcius): 
    return ((temperature_celcius * 9/5) + 32)

def FahrenheitToCelcius(temperature_fahrenheit): 
    return ((temperature_fahrenheit - 32) * 5/9)