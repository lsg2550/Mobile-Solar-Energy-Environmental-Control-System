import RPi.GPIO as GPIO
import time

# GPIO Init
GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)
GPIO.cleanup()
dht11 = 22
timeoutCount = 100

def bin2dec(string_num): return str(int(string_num, 2))

while True:
    # 'Open' sensor to gather data
    GPIO.setup(dht11, GPIO.OUT)
    GPIO.output(dht11, GPIO.HIGH)
    time.sleep(0.050)
    GPIO.output(dht11, GPIO.LOW)
    time.sleep(0.025)

    # Read data
    GPIO.setup(dht11, GPIO.IN, pull_up_down = GPIO.PUD_UP)
    unchangedCount = 0
    tempLast = -1
    data = []
    while True:
        tempCurr = GPIO.input(dht11)
        data.append(tempCurr)
        if tempLast != tempCurr:
            unchangedCount = 0
            tempLast = tempCurr
        else:
            unchangedCount += 1
            if unchangedCount > timeoutCount: break

    TemperatureBit = ""
    HumidityBit = ""
    bit_count = 0
    count = 0
    crc = ""
    tmp = 0

    try:
        # Find State Positions
        STATE_INIT_PULL_DOWN = 1
        STATE_INIT_PULL_UP = 2
        STATE_DATA_FIRST_PULL_DOWN = 3
        STATE_DATA_PULL_UP = 4
        STATE_DATA_PULL_DOWN = 5
        state = STATE_INIT_PULL_DOWN
        lengths = []
        currentLength = 0
        
        for i in range(len(data)):
            current = data[i]
            currentLength += 1
            
            if state == STATE_INIT_PULL_DOWN:
                if current == GPIO.LOW:
                    state = STATE_INIT_PULL_UP
                    continue
                else: continue
                
            if state == STATE_INIT_PULL_UP:
                if current == GPIO.HIGH:
                    state = STATE_DATA_FIRST_PULL_DOWN
                    continue
                else: continue
                
            if state == STATE_DATA_FIRST_PULL_DOWN:
                if current == GPIO.LOW:
                    state = STATE_DATA_PULL_UP
                    continue
                else: continue
                
            if state == STATE_DATA_PULL_UP:
                if current == GPIO.HIGH:
                    currentLength = 0
                    state = STATE_DATA_PULL_DOWN
                    continue
                else: continue
                
            if state == STATE_DATA_PULL_DOWN:
                if current == GPIO.LOW:
                    lengths.append(currentLength)
                    state = STATE_DATA_PULL_UP
                    continue
                else: continue
        
        if len(lengths) != 40:
            print("MISSING DATA")
            continue
        
        # Find Bits
        shortest_pull_up = 1000
        longest_pull_up = 0
        bits = []
        for i in range(0, len(lengths)):
            length = lengths[i]
            if length < shortest_pull_up: shortest_pull_up = length
            if length > longest_pull_up: longest_pull_up = length
        halfwayPeriod = shortest_pull_up + ((longest_pull_up - shortest_pull_up) / 2)
        for i in range(0, len(lengths)):
            bit = False
            if lengths[i] > halfwayPeriod: bit = True
            bits.append(bit)
        
        # Convert Bits to Bytes
        bytes = []
        byte = 0
        for i in range(0, len(bits)):
            byte = byte << 1
            if bits[i]: byte = byte | 1
            else: byte = byte | 0
            if (i + 1)%8 == 0:
                bytes.append(byte)
                byte = 0
        checksum = bytes[0] + bytes[1] + bytes[2] + bytes[3] & 255
        if bytes[4] != checksum:
            print("CHECKSUM ERROR")
            continue
        
        print("No Error, {}, {}".format(str(bytes[2]), str(bytes[0])))
    except Exception as e:
        print(e)
    time.sleep(1)
