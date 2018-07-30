#import
import imutils
from imutils.video import VideoStream
from threading import Thread
from enum import Enum
import datetime
import time
import cv2

class LowerBound(Enum):
    LENIENT = 0.5
    STRICT = 0.1

def CaptureImage(currentTime, frame):
    cv2.imwrite("capture" + currentTime + ".jpg", frame)

def Main(fps, LowerBound):
    #Initialize
    vs = VideoStream(src = 0).start()
    firstFrame = None
    minArea = 500
    startTime = time.time()
    lowerTimebound = fps - LowerBound.value #Has to be greater by this fps minus # - this lowerbound is used to be more strict on the timer if it is capturing multiple pictures around the same time
    upperTimebound = fps #Has to be less than given fps

    #Recording While Loop
    while True:
        #Read frame
        frame = vs.read()
        text = "Clear"

        #Program is not recording, so break
        if frame is None: break

        #Convert frame to specified size and perform color and blur operations for comparisons
        frame = imutils.resize(frame, width = minArea)
        frameGray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
        frameGray = cv2.GaussianBlur(frameGray, (21, 21), 0)

        #Initial run only - no previous frame to compare so set the converted frame to the firstFrame and continue to the next iteration of the loop
        if firstFrame is None:
            firstFrame = frameGray
            continue

        #Compute the difference between the first frame and the new frame - Perform image operations to find contours
        frameDelta = cv2.absdiff(firstFrame, frameGray)
        thresh = cv2.threshold(frameDelta, 25, 255, cv2.THRESH_BINARY)[1]
        thresh = cv2.dilate(thresh, None, iterations = 2)
        contours = cv2.findContours(thresh.copy(), cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
        contours = contours[0] if imutils.is_cv2() else contours[1]

        #Loop through the contours
        for c in contours:
            #If the contour is too small, move to the next one
            if cv2.contourArea(c) < minArea: continue
            
            #Generate text and bounding rectangles of the detected object for the view in the windows
            text = "Motion Detected"
            currentTime = datetime.datetime.now().strftime("%A %d %B %Y %I:%M:%S%p")
            (x, y, w, h) = cv2.boundingRect(c)
            cv2.rectangle(frame, (x,y), (x + w, y + h), (0, 255, 0), 2)
            cv2.putText(frame, "TUS Status: {}".format(text), (10, 20), cv2.FONT_HERSHEY_COMPLEX, 0.5, (0, 0, 255), 2)
            cv2.putText(frame, currentTime, (10, frame.shape[0] - 10), cv2.FONT_HERSHEY_SIMPLEX, 0.35, (0, 0, 255), 1)

            #Show Windows
            cv2.imshow("Security Feed", frame)
            #cv2.imshow("Thresh", thresh)
            #cv2.imshow("Frame Delta", frameDelta)

            #Determine if it has been the set amount of time, then Capture Image in Another Thread
            timer = (time.time() - startTime) % fps
            total = fps - timer
            if total <= upperTimebound and total >= lowerTimebound:
                sendThread = Thread(target=CaptureImage, args=(currentTime, frame))
                sendThread.start()

            #Quit program if the key 'q' is pressed
            if cv2.waitKey(1) & 0xFF == ord("esc"):
                break

    #Stop videostream and close all windows
    vs.stop()
    cv2.destroyAllWindows()

if __name__ == '__main__':
    Main(5.0, LowerBound.STRICT)