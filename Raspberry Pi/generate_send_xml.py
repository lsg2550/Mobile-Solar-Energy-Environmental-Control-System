# import
import os
import sys
import time
import json
import shutil
import requests
import connect_to_ftp as CTF
import read_analog_from_adc as RAFA
import motion_detection as MD
from threading import Thread
from xml.etree import ElementTree
from datetime import datetime
from pytz import timezone

# Initialize
# Create Storage Directories
TEMPORARY_STORAGE_DIRECTORY = "TempStorage/"
SENT_STORAGE_DIRECTORY = "SentStorage/"
if not os.path.isdir(TEMPORARY_STORAGE_DIRECTORY): os.mkdir(TEMPORARY_STORAGE_DIRECTORY)
if not os.path.isdir(SENT_STORAGE_DIRECTORY): os.mkdir(SENT_STORAGE_DIRECTORY)
DATE_AND_TIME_FORMAT = "%Y-%m-%d %H:%M:%S" # Date & Time Format for XML
RPID = 0 # RaspberryPi Identification Number (rpid)
pi_payload = {"rpid": RPID} # Payload for Server Confirmation
json_format = {} # JSON Format

def GetAndSendStatus(): # Send XML to Server
    try:
        for stored_file in sorted(os.listdir(TEMPORARY_STORAGE_DIRECTORY)):
            temporary_file = str(stored_file)

            if temporary_file.endswith(".json"):
                temp_storage_full_path = os.path.join(TEMPORARY_STORAGE_DIRECTORY, temporary_file)
                sent_storage_full_path = os.path.join(SENT_STORAGE_DIRECTORY, temporary_file)
                CTF.SendStatus(TEMPORARY_STORAGE_DIRECTORY + temporary_file)
                
                pi_payload["xmlfile"] = temporary_file
                server_confirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/piconfirm.php", params=pi_payload)
                print(server_confirmation.text.strip())
                pi_payload.pop("xmlfile")

                if server_confirmation.text.strip() == "OK":
                    print("File confirmed received!")
                    os.rename(temp_storage_full_path, sent_storage_full_path)
                else: break # Server did not receive or process the XML correctly
    except Exception as e: print("Could not connect to server...\nStoring status file into {}...\nError Received:{}".format(TEMPORARY_STORAGE_DIRECTORY, e))
    print("Status background thread done!")
# GetAndSendStatus() end

def GetAndSendImages():
    try: 
        detection_directory_contents = sorted(os.listdir(MD.DETECTION_DIRECTORY))
        for stored_frames in detection_directory_contents:
            if detection_directory_contents[-1] == stored_frames: break # Skip the last folder in case it is still being filled with images
            temporary_file_full_path = os.path.join(MD.DETECTION_DIRECTORY, stored_frames)

            for root, subfolders, files in sorted(os.walk(temporary_file_full_path)): 
                CTF.SendImages(root, files)
                
                pi_payload["capture"] = stored_frames
                server_confirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/piimageconfirm.php", params=pi_payload)
                print(server_confirmation.text.strip())
                pi_payload.pop("capture")

                if server_confirmation.text.strip() == "OK":
                    print("File and folders confirmed received!")
                    shutil.rmtree(root) # Delete Capture Folder
                else: break # Server did not receive or process the images correctly
    except Exception as e: print("Could not connect to server...\nImages were not sent...\nError Received:{}".format(e))
    print("Images background thread done!")
# GetAndSendImages() end

def Main():
    # Program Start Time
    START_TIME = time.time() # Initialize Program Start Time
    SEND_STATUS_THREAD = None # Thread for sending XML/JSON
    SEND_IMAGES_THREAD = None # Thread for sending detection images
    
    # Start Camera Thread
    CAMERA_THREAD = Thread(target=MD.Main, args=(START_TIME, ))
    CAMERA_THREAD.setDaemon(True)
    CAMERA_THREAD.start()

    while True:
        try: # Retrieve files with thresholds set by user
            print("Requesting threshold update from server...")
            server_threshold_confirmation = requests.get("https://remote-ecs.000webhostapp.com/index_files/pithresholdconfirm.php", params=pi_payload)
            
            if server_threshold_confirmation.text.strip() == "OK":
                CTF.RetrieveThreshold(RPID)
                threshold_file_name = str(RPID) + ".json"

                # Tell the server that we retrieved the file
                pi_payload["result"] = "OK"
                requests.get("https://remote-ecs.000webhostapp.com/index_files/piserverconfirm.php", params=pi_payload)
                pi_payload.pop("result")
            else: # Tell the server that we did not retrieve the file
                pi_payload["result"] = "NO"
                requests.get("https://remote-ecs.000webhostapp.com/index_files/piserverconfirm.php", params=pi_payload)
                pi_payload.pop("result")
                raise FileNotFoundError
        except Exception as e:
            print("Could not connect to server/Issue with server...\nError Received:{}".format(e))
            if os.path.exists(str(RPID) + ".json"):
                threshold_file_name = str(RPID) + ".json"
                print("Using previous thresholds...")
            else:
                threshold_file_name = "default.json"
                print("Using system default thresholds...")

        with open(threshold_file_name, "r") as thresholdfile: thresholds = json.loads(thresholdfile.read())
        threshold_voltage_lower = thresholds["voltagelower"]
        threshold_voltage_upper = thresholds["voltageupper"]
        threshold_current_lower = thresholds["currentlower"]
        threshold_current_upper = thresholds["currentupper"]
        threhsold_solar_panel_voltage_lower = thresholds["spvoltagelower"]
        threshold_solar_panel_voltage_upper = thresholds["spvoltageupper"]
        threshold_solar_panel_current_lower = thresholds["spcurrentlower"]
        threshold_solar_panel_current_upper = thresholds["spcurrentupper"]
        threshold_charge_controller_voltage_lower = thresholds["ccvoltagelower"]
        threshold_charge_controller_voltage_upper = thresholds["ccvoltageupper"]
        threshold_charge_controller_current_lower = thresholds["cccurrentlower"]
        threshold_charge_controller_current_upper = thresholds["cccurrentupper"]
        threshold_temperature_inner_lower = thresholds["temperatureinnerlower"]
        threshold_temperature_inner_upper = thresholds["temperatureinnerupper"]
        threshold_temperature_outer_lower = thresholds["temperatureouterlower"]
        threshold_temperature_outer_upper = thresholds["temperatureouterupper"]
        
        # Read from Sensors
        print("Reading from sensors...")
        sensor_status_dictionary = RAFA.ReadFromSensors(threshold_voltage_lower, threshold_voltage_upper,
                                                threshold_current_lower, threshold_current_upper,
                                                threhsold_solar_panel_voltage_lower, threshold_solar_panel_voltage_upper,
                                                threshold_solar_panel_current_lower, threshold_solar_panel_current_upper,
                                                threshold_charge_controller_voltage_lower, threshold_charge_controller_voltage_upper,
                                                threshold_charge_controller_current_lower, threshold_charge_controller_current_upper,
                                                threshold_temperature_inner_lower, threshold_temperature_inner_upper,
                                                threshold_temperature_outer_lower, threshold_temperature_outer_upper)
        print("Done reading from sensors...")

        # Generate Timestamps
        timestamp_for_log = datetime.now(timezone("UTC")).strftime(DATE_AND_TIME_FORMAT)
        timestamp_for_filename = timestamp_for_log.replace(":", "-")
        
        # Update jsonFormat
        json_format["log"] = str(timestamp_for_log)
        json_format["rpid"] = str(RPID)
        for key, value in sensor_status_dictionary.items(): json_format[key] = str(value)
            
        # Write and Close File
        status_filename = TEMPORARY_STORAGE_DIRECTORY + "status" + str(RPID) + "(" + timestamp_for_filename + ").json"
        with open(status_filename, "w+") as status: json.dump(json_format, status, indent = 4)
            
        # Send XML in new thread
        if SEND_STATUS_THREAD == None or not SEND_STATUS_THREAD.isAlive():
            SEND_STATUS_THREAD = Thread(target=GetAndSendStatus, args=())
            SEND_STATUS_THREAD.start()
            
        # Send images in new thread
        if SEND_IMAGES_THREAD == None or not SEND_IMAGES_THREAD.isAlive():
            SEND_IMAGES_THREAD = Thread(target=GetAndSendImages, args=())
            SEND_IMAGES_THREAD.start()

        # Wait for 60 seconds for the next read interval
        timer = (time.time() - START_TIME) % 60
        print("File transfer moved to a background thread...\nMain thread is now on standby for {0:.2} seconds...\n".format(str((60.0 - timer))))
        time.sleep(60.0 - timer)
    # while end
# Main() end

if __name__ == "__main__":
    print("Program Start")
    Main()