#import
import shutil
import imutils
from imutils.video import VideoStream
from threading import Thread
from datetime import datetime
from pytz import timezone
import time
import cv2
import os
import re

#Minute/Detect Directories
prevMinuteDir = "PrevMinuteDir/"
currMinuteDir = "CurrMinuteDir/"
detectionDir = "DetectDir/"

#Date & Time Format
dateAndTimeFormat = "%Y-%m-%d %H:%M:%S"
dateAndTimeFormatTwo = "%A %d %B %Y %I:%M:%S%p"

def CaptureIntrusion(filenameSafeCurrentTime, frameName, secondsThreshold):
    #Initialize
    detectionAndFileNamePath = detectionDir + filenameSafeCurrentTime
    if not os.path.isdir(detectionAndFileNamePath): os.mkdir(detectionAndFileNamePath)
    prevMinuteDirList = sorted(os.listdir(prevMinuteDir))
    currMinuteDirList = sorted(os.listdir(currMinuteDir))
    currFrameIndex = currMinuteDir.find(frameName)
    indexCounter = 0

    #Capture an image every N seconds before
    try:
        for currMinuteImg in currMinuteDirList[currFrameIndex:currFrameIndex - secondsThreshold:-1]:
            currMinuteImgFP = os.path.join(currMinuteDir, currMinuteImg)
            shutil.copy(currMinuteImgFP, detectionAndFileNamePath)
            indexCounter += 1
    except IndexError:
        try:
            sizeOfList = len(prevMinuteDirList)
            for prevMinuteImg in prevMinuteDirList[:sizeOfList - indexCounter:-1]:
                prevMinuteImgFP = os.path.join(prevMinuteDir, prevMinuteImg)
                shutil.copy(prevMinuteImgFP, detectionAndFileNamePath)
                indexCounter += 1
        except: pass #Movement must have been caught in the beginning of the loop

    #Capture an image every N seconds after
    timeoutMax = 15
    timeoutCounter = 0
    indexCounter = 0
    indexHour = 0
    indexMinute = 0
    indexSecond = 0
    strHour = ""
    strMinute = ""
    strSecond = ""
    while True:
        if indexCounter == secondsThreshold + 1: break
        if indexSecond == 0: 
            matches = re.findall(r'[0-9]{2}-[0-9]{2}-[0-9]{2}$', filenameSafeCurrentTime)
            splits = re.split(r'-', matches[0])
            indexHour = int(splits[0])
            indexMinute = int(splits[1])
            indexSecond = int(splits[2])
            #print(splits)
            continue

        #Get new image name
        strHour = str(indexHour)
        strMinute = str(indexMinute)
        strSecond = str(indexSecond)
        if len(strHour) == 1: strHour = "0" + strHour
        if len(strMinute) == 1: strMinute = "0" + strMinute
        if len(strSecond) == 1: strSecond = "0" + strSecond
        getSecondsAndClock = re.sub(r'[0-9]{2}-[0-9]{2}-[0-9]{2}$', strHour + "-" + strMinute + "-" + strSecond, filenameSafeCurrentTime) 
        frameFP = currMinuteDir + "capture (" + getSecondsAndClock + ").jpg"
        #print("{}:{}:{}\t{}".format(strHour, strMinute, strSecond, indexCounter))
        #print(frameFP)

        #Move image to minute directory
        time.sleep(1)
        if os.path.exists(frameFP): 
            shutil.copy(frameFP, detectionAndFileNamePath)
            
            #Time/Clock Checks
            if indexSecond + 1 == 60:
                indexSecond = 0 #Reset seconds to 0 
                if indexMinute + 1 == 60: 
                    indexMinute = 0 #Reset minutes to 0
                    if indexHour + 1 == 13:
                        indexHour = 1 #Reset hours to 1
                    else: indexHour += 1 #Increment hour
                else: indexMinute += 1 #Increment minute
            else: indexSecond += 1
            
            indexCounter += 1
            timeoutCounter = 0
        else: 
            timeoutCounter += 1
            if timeoutCounter == timeoutMax: break

def Main(programTime=None):
    #Initialize
    try: vs = VideoStream(src = 0).start()
    except:
        print("No recording device found...")
        return
    startTime = time.time() if programTime == None else programTime
    intrusionThread = None
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
            currentTime = datetime.now(timezone("UTC")).strftime(dateAndTimeFormat)
            (x, y, w, h) = cv2.boundingRect(c)
            cv2.rectangle(frame, (x,y), (x + w, y + h), (0, 255, 0), 2)
            cv2.putText(frame, "Status: {}".format(text), (10, 20), cv2.FONT_HERSHEY_COMPLEX, 0.5, (0, 0, 255), 2)
            cv2.putText(frame, currentTime, (10, frame.shape[0] - 10), cv2.FONT_HERSHEY_SIMPLEX, 0.35, (0, 0, 255), 1)
            #cv2.imshow("Security Feed", frame)

            #Write image
            filenameSafeCurrentTime = currentTime.replace(":", "-")
            currFrameName = "capture (" + filenameSafeCurrentTime + ").jpg"
            currFrameNameFP = currMinuteDir + currFrameName
            cv2.imwrite(currFrameNameFP, frame)
            
            #Capture intrusion thread
            if intrusionThread == None or not intrusionThread.isAlive():
                intrusionThread = Thread(target = CaptureIntrusion, args = (filenameSafeCurrentTime, currFrameName, 4, ))
                intrusionThread.start()
        #End for loop

        #Get timers and time (long) for minute directory check and image capture 
        timeTime = time.time()        
        timerMinute = (timeTime - startTime) % 60
        timerSecond = (timeTime - startTime) % 1
        totalMinute = 60 - timerMinute
        totalSecond = 1 - timerSecond
        #print("Total Minute: {}".format(str(totalMinute)))
        #print("Total Second: {}".format(str(totalSecond)))

        #Minute directory check - Move files in currMinuteDir to prevMinuteDir, if prevMinuteDir exists, delete all contents and store new files in there (new thread)
        if totalMinute < 60 and totalMinute >= 59.9:
            prevMinuteDirList = os.listdir(prevMinuteDir)
            currMinuteDirList = os.listdir(currMinuteDir)
            for prevImg in prevMinuteDirList: os.unlink(os.path.join(prevMinuteDir, prevImg))
            for currImg in currMinuteDirList: os.rename(os.path.join(currMinuteDir, currImg), prevMinuteDir + currImg)

        #Capture Image
        if totalSecond < 1 and totalSecond >= 0.9:
            currentTime = datetime.now(timezone("UTC")).strftime(dateAndTimeFormat)
            filenameSafeCurrentTime = currentTime.replace(":", "-")
            currFrameName = "capture (" + filenameSafeCurrentTime + ").jpg"
            currFrameNameFP = currMinuteDir + currFrameName
            cv2.imwrite(currFrameNameFP, frame)
    #End while loop

    #Stop videostream and close all windows
    vs.stop()
    cv2.destroyAllWindows()

if __name__ == '__main__':
    try: shutil.rmtree(prevMinuteDir)
    except FileNotFoundError: pass
    finally: os.mkdir(prevMinuteDir)
    try: shutil.rmtree(currMinuteDir)
    except FileNotFoundError: pass
    finally: os.mkdir(currMinuteDir)
    if not os.path.isdir(detectionDir): os.mkdir(detectionDir)
    Main()
else:
    try: shutil.rmtree(prevMinuteDir)
    except FileNotFoundError: pass
    finally: os.mkdir(prevMinuteDir)
    try: shutil.rmtree(currMinuteDir)
    except FileNotFoundError: pass
    finally: os.mkdir(currMinuteDir)
    if not os.path.isdir(detectionDir): os.mkdir(detectionDir) 