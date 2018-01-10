from sense_hat import SenseHat;

#Sense Hat Object
sense = SenseHat();

#Main Colors
red = (255, 0, 0);
green = (0, 255, 0);
blue = (0, 0, 255);
white = (255, 255, 255);
black = (0, 0, 0);

#Other Colors
blue_green = (0, 255, 255);

#Function
sense.set_pixel(2, 0, blue_green);
sense.set_pixel(5, 0, blue_green);

sense.set_pixel(3, 3, blue_green);
sense.set_pixel(3, 4, blue_green);
sense.set_pixel(4, 3, blue_green);
sense.set_pixel(4, 4, blue_green);

sense.set_pixel(3, 7, blue_green);
sense.set_pixel(4, 7, blue_green);
sense.set_pixel(2, 6, blue_green);
sense.set_pixel(5, 6, blue_green);
sense.set_pixel(1, 6, blue_green);
sense.set_pixel(6, 6, blue_green);
sense.set_pixel(0, 5, blue_green);
sense.set_pixel(7, 5, blue_green);

#Clear SenseHat
sense.clear();
