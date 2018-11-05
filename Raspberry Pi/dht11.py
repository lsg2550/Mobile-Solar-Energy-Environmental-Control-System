import RPi.GPIO as GPIO
import time

# GPIO Devices = GPIO Pin #
GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)
GPIO.cleanup()
DHT11 = 22

while True:
    global DHT11

    # 'Open' sensor to gather data
    GPIO.setup(DHT11, GPIO.OUT)
    GPIO.output(DHT11, GPIO.HIGH)
    time.sleep(0.025)
    GPIO.output(DHT11, GPIO.LOW)
    time.sleep(0.020)
    GPIO.setup(DHT11, GPIO.IN, pull_up_down = GPIO.PUD_UP)

    # Read data
    timeoutMaxCount = 100
    timeoutCounter = 0
    tempPrev = -1
    data = []
    while True:
        tempCurr = GPIO.input(DHT11)
        data.append(tempCurr)
        if tempPrev != tempCurr:
            timeoutCounter = 0
            tempPrev = tempCurr
        else:
            timeoutCounter += 1
            if timeoutCounter > timeoutMaxCount: break

    # Find Pull-Up-Down Position Lengths
    ST_INIT_PUD_DOWN = 1
    ST_INIT_PUD_UP = 2
    ST_DATA_FIRST_PUD_DOWN = 3
    ST_DATA_PUD_UP = 4
    ST_DATA_PUD_DOWN = 5
    initialState = ST_INIT_PUD_DOWN
    listOfStateLengths = []
    lengthCounter = 0
    for i in range(len(data)):
        currentPUDPosition = data[i]
        lengthCounter += 1
        if initialState == ST_INIT_PUD_DOWN:
            if currentPUDPosition == GPIO.LOW: initialState = ST_INIT_PUD_UP
            continue
        if initialState == ST_INIT_PUD_UP:
            if currentPUDPosition == GPIO.HIGH: initialState = ST_DATA_FIRST_PUD_DOWN
            continue
        if initialState == ST_DATA_FIRST_PUD_DOWN:
            if currentPUDPosition == GPIO.LOW: initialState = ST_DATA_PUD_UP
            continue
        if initialState == ST_DATA_PUD_UP:
            if currentPUDPosition == GPIO.HIGH:
                initialState = ST_DATA_PUD_DOWN
                lengthCounter = 0
            continue
        if initialState == ST_DATA_PUD_DOWN:
            if currentPUDPosition == GPIO.LOW:
                initialState = ST_DATA_PUD_UP
                listOfStateLengths.append(lengthCounter)
            continue
    if len(listOfStateLengths) != 40: 
        print("MISSING DATA ERROR, {}, {}".format(0, 0))
        continue
    
    # Find Bits
    shortestPUD = float('Inf')
    longestPUD = 0
    listOfBits = []
    for i in range(0, len(listOfStateLengths)):
        length = listOfStateLengths[i]
        if length < shortestPUD: shortestPUD = length
        if length > longestPUD: longestPUD = length
    medianPUDPosition = shortestPUD + ((longestPUD - shortestPUD) / 2)
    for i in range(0, len(listOfStateLengths)):
        if listOfStateLengths[i] > medianPUDPosition: bit = True
        else: bit = False
        listOfBits.append(bit)
    
    # Convert Bits to Bytes
    listOfBytes = []
    byte = 0
    for i in range(0, len(listOfBits)):
        byte = byte << 1
        if listOfBits[i]: byte = byte | 1
        else: byte = byte | 0
        if (i + 1) % 8 == 0:
            listOfBytes.append(byte)
            byte = 0
    checksum = listOfBytes[0] + listOfBytes[1] + listOfBytes[2] + listOfBytes[3] & 255
    if listOfBytes[4] != checksum: 
        print("CHECKSUM ERROR, {}, {}".format(0, 0))
        continue

    # Return Status, Temperature, Humidity
    print("NO ERROR, {}, {}".format(str(listOfBytes[2]), str(listOfBytes[0])))
    time.sleep(1)