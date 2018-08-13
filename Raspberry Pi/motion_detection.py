#import
import shutil
import imutils
from imutils.video import VideoStream
from threading import Thread
import datetime
import time
import cv2
import os

#Create/GC Minute Directories
prevMinuteDir = "PrevMinuteDir/"
currMinuteDir = "CurrMinuteDir/"
try: shutil.rmtree(prevMinuteDir)
except FileNotFoundError: pass
finally: os.mkdir(prevMinuteDir)
try: shutil.rmtree(currMinuteDir)
except FileNotFoundError: pass
finally: os.mkdir(currMinuteDir)

def CaptureIntrusion(filenameSafeCurrentTime, frameName, secondsThreshold, startTime):
    #Initialize
    if not os.path.isdir(filenameSafeCurrentTime): os.mkdir(filenameSafeCurrentTime)
    prevMinuteDirList = sorted(os.listdir(prevMinuteDir))
    currMinuteDirList = sorted(os.listdir(currMinuteDir))
    currFrameIndex = currMinuteDir.find(frameName)
    indexCounter = 0

    #Capture an image every N seconds before
    try:
        for currMinuteImg in currMinuteDirList[currFrameIndex:currFrameIndex - secondsThreshold:-1]:
            currMinuteImgFP = os.path.join(currMinuteDir, currMinuteImg)
            shutil.copy(currMinuteImgFP, filenameSafeCurrentTime)
            indexCounter += 1
    except IndexError:
        sizeOfList = len(prevMinuteDirList)
        for prevMinuteImg in prevMinuteDirList[:sizeOfList - indexCounter:-1]:
            prevMinuteImgFP = os.path.join(prevMinuteDir, prevMinuteImg)
            shutil.copy(prevMinuteImgFP, filenameSafeCurrentTime)
            indexCounter += 1

    #Capture an image every N seconds after
    indexCounter = 0
    while True:
        if indexCounter == secondsThreshold: break

        #Read frame
        frame = vs.read()
        if frame is None: break #Program is not recording, so break
        frame = imutils.resize(frame, width = minArea) #Convert frame to specified size

        #Get current time (long) for minute directory check and image capture 
        timer = (time.time() - startTime) % 1
        totalSecond = 1 - timer
        if totalSecond < 1 and totalSecond >= 0.9:
            currentTime = datetime.datetime.now().strftime("%A %d %B %Y %I:%M:%S%p")
            filenameSafeCurrentTime = currentTime.replace(":", "-")
            frameName = currMinuteDir + "capture (" + filenameSafeCurrentTime + ").jpg"
            cv2.imwrite(frameName, frame)
            shutil.copy(frameName, filenameSafeCurrentTime)
        
        #Increment Counter
        indexCounter += 1

def Main():
    #Initialize
    vs = VideoStream(src = 0).start()
    startTime = time.time()
    firstFrame = None
    minArea = 500   

    #Begin monitoring
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
            if cv2.contourArea(c) < minArea: continue #If the contour is too small, move to the next one

            #Generate text and bounding rectangles of the detected object for the view in the windows, then show window
            text = "Motion Detected"
            currentTime = datetime.datetime.now().strftime("%A %d %B %Y %I:%M:%S%p")
            (x, y, w, h) = cv2.boundingRect(c)
            cv2.rectangle(frame, (x,y), (x + w, y + h), (0, 255, 0), 2)
            cv2.putText(frame, "Status: {}".format(text), (10, 20), cv2.FONT_HERSHEY_COMPLEX, 0.5, (0, 0, 255), 2)
            cv2.putText(frame, currentTime, (10, frame.shape[0] - 10), cv2.FONT_HERSHEY_SIMPLEX, 0.35, (0, 0, 255), 1)
            cv2.imshow("Security Feed", frame)

            #Capture intrusion
            filenameSafeCurrentTime = currentTime.replace(":", "-")
            currFrameName = currMinuteDir + "capture (" + filenameSafeCurrentTime + ").jpg"
            cv2.imwrite(currFrameName, frame)
            # CaptureIntrusion(filenameSafeCurrentTime, currFrameName, 4)
        #End for loop

        #Get current time (long) for minute directory check and image capture 
        timeTime = time.time()

        #Minute directory check - Move files in currMinuteDir to prevMinuteDir, if prevMinuteDir exists, delete all contents and store new files in there (new thread)
        timerMinute = (timeTime - startTime) % 60
        totalMinute = 60 - timerMinute
        if totalMinute < 60 and totalMinute >= 59.99:
            prevMinuteDirList = os.listdir(prevMinuteDir)
            currMinuteDirList = os.listdir(currMinuteDir)
            for prevImg in prevMinuteDirList: os.unlink(os.path.join(prevMinuteDir, prevImg))
            for currImg in currMinuteDirList: os.rename(os.path.join(currMinuteDir, currImg), prevMinuteDir + currImg)

        #Capture Image
        timerSecond = (timeTime - startTime) % 1
        totalSecond = 1 - timerSecond
        if totalSecond < 1 and totalSecond >= 0.9:
            currentTime = datetime.datetime.now().strftime("%A %d %B %Y %I:%M:%S%p")
            filenameSafeCurrentTime = currentTime.replace(":", "-")
            frameName = currMinuteDir + "capture (" + filenameSafeCurrentTime + ").jpg"
            cv2.imwrite(frameName, frame)
    #End while loop

    #Stop videostream and close all windows
    vs.stop()
    cv2.destroyAllWindows()

if __name__ == '__main__':
    Main()