from sense_hat import SenseHat;
from time import sleep;

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
textSpeed = 0.05;
textMessage = "Hello world!";
textMessage_Pt1 = "Hello";
textMessage_Pt2 = "Is it me you're looking for.";

sense.show_message(textMessage, textSpeed, text_colour = white, back_colour = black);
sleep(1);
sense.show_message(textMessage_Pt1, textSpeed, text_colour = red, back_colour = black);
sleep(1);
sense.show_message(textMessage_Pt2, textSpeed, text_colour = blue_green, back_colour = black);
sense.clear();