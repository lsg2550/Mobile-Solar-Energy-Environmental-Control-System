from pathlib import Path

#HOME_PATH = str(Path.home()) + "/Mobile-Solar-Energy-Environmental-Control-System/Raspberry-Pi/"
HOME_PATH = "/home/pi/Mobile-Solar-Energy-Environmental-Control-System/Raspberry-Pi/" # Due to the RPi having to use an older kernel, I believe I am unable to use an updated python where Path.home() works.
RPID = 0
PIPAYLOAD = {"rpid": RPID}