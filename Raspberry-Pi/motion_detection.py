# import
from picamera.array import PiRGBArray # PiCamera Module - Specifically the type of frames we want
from picamera import PiCamera # PiCamera Module
from threading import Thread # Threading
from datetime import datetime # DateTime
from pytz import timezone # Timezone
import notify_server # Used to send a request to the CMS to notify the user of motion detection
import global_var # Variables used across the program 
import numpy # Matrix Operations for Image Processing
import shutil # File Operations
import imutils # Image Utilities
import time # Time
import cv2 # OpenCV
import os # System Calls
import re # Regex

# Initialize directories and variables
PREVIOUS_MINUTE_DIRECTORY = "prevminutedir/"
CURRENT_MINUTE_DIRECTORY = "currminutedir/"
DETECTION_DIRECTORY = "detectdir/"
CLARITY_DIRECTORY = "claritydir/"
DATE_AND_TIME_FORMAT = "%Y-%m-%d %H:%M:%S" # Date & time format for file names and image embedding

# Camera Variables
RAW_CAMERA = None # Camera
RAW_CAPTURE = None # Capture Object
FRAMERATE = 30 # Max Framerate
WIDTH = 640 # Max Width
HEIGHT = 480 # Max Height

# Camera - Clarity Variables
CLARITY_CAPTURE = None # Clarity Capture Object
CLARITY_CAPTURE_IMAGE_NAME = str(global_var.RPID) + "_clarity_[ts]_[val].jpg" # Name of Clarity Image

def ClarityCapture():
    # Capture Image and Threshold it
    frame_capture_gray = cv2.cvtColor(CLARITY_CAPTURE, cv2.COLOR_BGR2GRAY)
    ret, frame_threshold = cv2.threshold(frame_capture_gray, 128, 255, cv2.THRESH_BINARY)
    
    # Calculate non-zero ratio (intention/assumption is that a clear sky will be brighter in color than a (dark/light) cloud or objects it picks up)
    # Perhaps in the future, a better implementation would be to have an even more detailed image processing code to identify objects and ignore their presence
    non_zero_count = cv2.countNonZero(frame_threshold)
    frame_size_total = frame_threshold.shape[0] * frame_threshold.shape[1]
    clear_ratio = 100 * (non_zero_count / float(frame_size_total))

    # Write image out
    current_time = datetime.now(timezone("America/Chicago")).strftime(DATE_AND_TIME_FORMAT)
    filename_safe_current_time = current_time.replace(":", "-")
    filename_clarity = CLARITY_CAPTURE_IMAGE_NAME
    filename_clarity = filename_clarity.replace("ts", filename_safe_current_time)
    filename_clarity = filename_clarity.replace("val", str(clear_ratio))
    cv2.imwrite(CLARITY_DIRECTORY + filename_clarity, frame_threshold)

def CaptureIntrusion(filenameSafeCurrentTime, frameName, secondsThreshold):
    # Create a detection directory for this instance of motion detected
    detection_and_filename_path = DETECTION_DIRECTORY + str(global_var.RPID) + "_[" + filenameSafeCurrentTime + "]" 
    if not os.path.isdir(detection_and_filename_path): os.mkdir(detection_and_filename_path)

    # Load names of files in the previous/current directories
    previous_minute_directory_list = sorted(os.listdir(PREVIOUS_MINUTE_DIRECTORY))
    current_minute_directory_list = sorted(os.listdir(CURRENT_MINUTE_DIRECTORY))
    current_frame_index = CURRENT_MINUTE_DIRECTORY.find(frameName) # Find the frame index where motion was detected
    index_counter = 0 # Counter to keep track of how many frames we collect before and after motion detection

    # Grab N frames before motion was detected
    try:
        for current_minute_frame in current_minute_directory_list[current_frame_index:current_frame_index - secondsThreshold:-1]:
            current_minute_frame_full_path = os.path.join(CURRENT_MINUTE_DIRECTORY, current_minute_frame)
            shutil.copy(current_minute_frame_full_path, detection_and_filename_path)
            index_counter += 1
    except IndexError as ie: # We've reached EoF for the current directory, move on to the prev (if it exists)
        try:
            size_of_previous_directory_list = len(previous_minute_directory_list)
            for previous_minute_frame in previous_minute_directory_list[:size_of_previous_directory_list - index_counter:-1]:
                previous_minute_frame_full_path = os.path.join(PREVIOUS_MINUTE_DIRECTORY, previous_minute_frame)
                shutil.copy(previous_minute_frame_full_path, detection_and_filename_path)
                index_counter += 1
        except Exception as e: pass # Movement has been caught in the beginning of the loop (or some other issue occured) - thus there are no images to grab in the previous directory

    # Grab N frames after motion was detected
    timeout_max = 15
    timeout_counter = 0
    index_counter = 0
    index_hour = 0
    index_minute = 0
    index_second = 0
    string_hour = ""
    string_minute = ""
    string_second = ""
    while True:
        if index_counter == secondsThreshold + 1: break # If the N frames has been collected, break
        # Find current time (hour-minute-second), then split all 3 into an array [hour, minute, second]
        if index_second == 0: 
            matches = re.findall(r'[0-9]{2}-[0-9]{2}-[0-9]{2}$', filenameSafeCurrentTime)
            splits = re.split(r'-', matches[0])
            index_hour = int(splits[0])
            index_minute = int(splits[1])
            index_second = int(splits[2])
            # print(splits)
            continue

        # Get new image name
        string_hour = str(index_hour)
        string_minute = str(index_minute)
        string_second = str(index_second)
        if len(string_hour) == 1: string_hour = "0" + string_hour
        if len(string_minute) == 1: string_minute = "0" + string_minute
        if len(string_second) == 1: string_second = "0" + string_second
        get_seconds_and_clock = re.sub(r'[0-9]{2}-[0-9]{2}-[0-9]{2}$', string_hour + "-" + string_minute + "-" + string_second, filenameSafeCurrentTime) 
        frame_full_path = CURRENT_MINUTE_DIRECTORY + str(global_var.RPID) + "_capture_[" + get_seconds_and_clock + "].jpg"
        # print(frame_full_path)

        # Move image to detection directory
        time.sleep(0.1)
        if os.path.exists(frame_full_path): 
            shutil.copy(frame_full_path, detection_and_filename_path)
            
            #Time/Clock Checks
            if index_second + 1 == 60:
                index_second = 0 # Reset seconds to 0 
                if index_minute + 1 == 60: 
                    index_minute = 0 # Reset minutes to 0
                    if index_hour + 1 == 13: index_hour = 1 # Reset hours to 1
                    else: index_hour += 1 # Increment hour
                else: index_minute += 1 # Increment minute
            else: index_second += 1
            index_counter += 1
            timeout_counter = 0
        else: 
            timeout_counter += 1
            if timeout_counter == timeout_max: break
            
    # Reqest Notification from CMS
    notify_server.notification_for_motion()

def Main(programTime=None):
    # Set globals
    global RAW_CAMERA
    global RAW_CAPTURE
    global CLARITY_CAPTURE
    
    try:
        RAW_CAMERA = PiCamera()
        RAW_CAMERA.resolution = (WIDTH, HEIGHT)
        RAW_CAMERA.framerate = FRAMERATE
        RAW_CAPTURE = PiRGBArray(RAW_CAMERA, size=(WIDTH, HEIGHT))
    except Exception as e: print("Exception Occured: {}".format(e))

    # Initialize/Synchronize program time
    START_TIME = time.time() if programTime == None else programTime
    INTRUSION_THREAD = None # Thread for creating detection directories when motion is detected
    first_frame = None # This frame is used to compare against the next frame to determine changes (or motion) between the frames

    # Begin monitoring
    frame_counter = 0
    for image in RAW_CAMERA.capture_continuous(RAW_CAPTURE, format="bgr", use_video_port=True):
        frame = image.array
        CLARITY_CAPTURE = frame.copy()

        # Convert frame to specified size and perform color and blur operations for comparisons
        frame_gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
        #frame_gray = cv2.GaussianBlur(frame_gray, (21, 21), 0)

        # Initial run only - no previous frame to compare so set the converted frame to the firstFrame and continue to the next iteration of the loop
        if first_frame is None:
            first_frame = frame_gray
            RAW_CAPTURE.truncate(0)
            continue

        # Compute the difference between the first frame and the new frame - Perform image operations to find contours
        frame_delta = cv2.absdiff(first_frame, frame_gray)
        thresh = cv2.threshold(frame_delta, 25, 255, cv2.THRESH_BINARY)[1]
        thresh = cv2.dilate(thresh, None, iterations = 2)
        contours = cv2.findContours(thresh.copy(), cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
        contours = contours[0] if imutils.is_cv2() else contours[1]

        # Loop through the contours
        for contour in contours:
            if cv2.contourArea(contour) < WIDTH: continue # If the contour is too small, move to the next one

            # Generate text and bounding rectangles of the detected object for the view in the windows, then show window
            current_time = datetime.now(timezone("America/Chicago")).strftime(DATE_AND_TIME_FORMAT)
            (x, y, w, h) = cv2.boundingRect(contour)
            cv2.rectangle(frame, (x,y), (x + w, y + h), (0, 255, 0), 2)
            cv2.putText(frame, current_time, (10, frame.shape[0] - 10), cv2.FONT_HERSHEY_SIMPLEX, 0.35, (125, 0, 255), 1)

            # Write image
            filename_safe_current_time = current_time.replace(":", "-")
            current_frame_name = str(global_var.RPID) + "_capture_[" + filename_safe_current_time + "].jpg"
            current_frame_name_full_path = CURRENT_MINUTE_DIRECTORY + current_frame_name
            cv2.imwrite(current_frame_name_full_path, frame)
            
            # Capture intrusion thread
            if INTRUSION_THREAD == None or not INTRUSION_THREAD.isAlive():
                INTRUSION_THREAD = Thread(target = CaptureIntrusion, args = (filename_safe_current_time, current_frame_name, 4, ))
                INTRUSION_THREAD.setDaemon(True)
                INTRUSION_THREAD.start()
                
            # Clear Stream
            RAW_CAPTURE.truncate(0)
        # End contours for loop

        # Get timers and time (long) for minute directory check and image capture 
        timer_time = time.time()        
        timer_minute = (timer_time - START_TIME) % 60
        timer_second = (timer_time - START_TIME) % 1
        #total_timer_minute = 60 - timer_minute
        #total_timer_second = 1 - timer_second
        #print("Total Minute: {}".format(str(timer_minute)))
        #print("Total Second: {}".format(str(timer_second)))

        # Minute directory check - Move files in currMinuteDir to prevMinuteDir, if prevMinuteDir exists, delete all contents and store new files in there (new thread)
        if timer_minute >= 59.5:
            previous_minute_directory_list = os.listdir(PREVIOUS_MINUTE_DIRECTORY)
            current_minute_directory_list = os.listdir(CURRENT_MINUTE_DIRECTORY)
            for previous_frame in previous_minute_directory_list:
                print(os.path.join(PREVIOUS_MINUTE_DIRECTORY, previous_frame))
                os.unlink(os.path.join(PREVIOUS_MINUTE_DIRECTORY, previous_frame))
            for current_frame in current_minute_directory_list:
                print(os.path.join(CURRENT_MINUTE_DIRECTORY, current_frame))
                os.rename(os.path.join(CURRENT_MINUTE_DIRECTORY, current_frame), os.path.join(PREVIOUS_MINUTE_DIRECTORY, current_frame))
                print(os.path.join(PREVIOUS_MINUTE_DIRECTORY, current_frame))
            ClarityCapture()
            time.sleep(0.5)

        # Capture Image
        if timer_second >= 0.9:
            current_time = datetime.now(timezone("America/Chicago")).strftime(DATE_AND_TIME_FORMAT)
            filename_safe_current_time = current_time.replace(":", "-")
            current_frame_name = str(global_var.RPID) + "_capture_[" + filename_safe_current_time + "].jpg"
            current_frame_name_full_path = CURRENT_MINUTE_DIRECTORY + current_frame_name
            cv2.imwrite(current_frame_name_full_path, frame)
        
        # Clear Stream
        RAW_CAPTURE.truncate(0)
    # End while loop
# Main() End0

if __name__ == '__main__':
    try: shutil.rmtree(PREVIOUS_MINUTE_DIRECTORY)
    except FileNotFoundError: pass
    finally: os.mkdir(PREVIOUS_MINUTE_DIRECTORY)
    try: shutil.rmtree(CURRENT_MINUTE_DIRECTORY)
    except FileNotFoundError: pass
    finally: os.mkdir(CURRENT_MINUTE_DIRECTORY)
    if not os.path.isdir(DETECTION_DIRECTORY): os.mkdir(DETECTION_DIRECTORY)
    if not os.path.isdir(CLARITY_DIRECTORY): os.mkdir(CLARITY_DIRECTORY)
    Main()
else:
    try: shutil.rmtree(PREVIOUS_MINUTE_DIRECTORY)
    except FileNotFoundError: pass
    finally: os.mkdir(PREVIOUS_MINUTE_DIRECTORY)
    try: shutil.rmtree(CURRENT_MINUTE_DIRECTORY)
    except FileNotFoundError: pass
    finally: os.mkdir(CURRENT_MINUTE_DIRECTORY)
    if not os.path.isdir(DETECTION_DIRECTORY): os.mkdir(DETECTION_DIRECTORY)
    if not os.path.isdir(CLARITY_DIRECTORY): os.mkdir(CLARITY_DIRECTORY)