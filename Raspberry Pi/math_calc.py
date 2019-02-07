def ConvertVolts(data, actual_voltage, actual_drop, place): # Used by Voltage Divider
    volts = (data / float(1023)) * (actual_voltage/actual_drop)
    volts = round(volts, place)
    return volts

def ConvertAmps(data, actual_voltage, actual_gain, place): # Used by Operational Amplifers
    volts_per_count = actual_voltage / 1023
    amp_per_millivolt = 100 / 75
    counts = volts_per_count * data
    amps = (((counts * volts_per_count) / actual_gain) * 1023) * amp_per_millivolt # (data / float(1023)) * (actual_voltage / actual_gain) * (100/0.75) 
    amps = round(amps, place)
    return amps

def CelciusToFahrenheit(temperature_celcius): 
    return ((temperature_celcius * 9/5) + 32)

def FahrenheitToCelcius(temperature_fahrenheit): 
    return ((temperature_fahrenheit - 32) * 5/9)