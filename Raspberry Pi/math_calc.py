def ConvertVolts(data, place): 
    return (data / float(1023)) * 25

def ConvertAmps(data, place): 
    return (data / float(1023)) * (5 / 41) * (100/0.75)

def CelciusToFahrenheit(temperature_celcius): 
    return ((temperature_celcius * 9/5) + 32)

def FahrenheitToCelcius(temperature_fahrenheit): 
    return ((temperature_fahrenheit - 32) * 5/9)