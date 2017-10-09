from sense_hat import SenseHat

#Sense Hat Object
sense = SenseHat();

#Humidity
humidity = sense.humidity;
humidity_value = 64 * humidity / 100;
sense.show_message(str(humidity_value), 0.3, (0, 255, 255), (0,0,0));
    