#import
import socketserver

#Init - Server Information
serverName = "127.0.0.1"
port = 43525
serverPassword = "123456"
isClientConnected = False

#Init - Other
secretArray = ["note1", "renewable", "fossil", "2020"]
secretArrayCount = 0

class MyTCPHandler(socketserver.StreamRequestHandler):

	def handle(self):
		global isClientConnected
		global secretArrayCount

		try:
			while True:
				if isClientConnected == False:
					self.wfile.write("Please enter the password: ".encode('utf-8'))
		
				self.data = self.rfile.readline().strip().decode('utf-8') #Read Client Input	
				#print(self.data)		

				if self.data == serverPassword and isClientConnected == False: #Case-Sensitive
					isClientConnected = True
					self.wfile.write("GOODINPUT".encode('utf-8'))
				elif self.data == "Y" and secretArrayCount < 4 and isClientConnected == True: #Y = More
					self.wfile.write(secretArray[secretArrayCount].encode('utf-8'))
					secretArrayCount += 1
				elif self.data == "Y" and secretArrayCount > 3 and isClientConnected == True: #Y = More
					self.wfile.write("NOMORE".encode('utf-8'))
					break
				elif self.data == "QUIT":
					self.wfile.write("QUIT".encode('utf-8'))
					break			
				else:
					self.wfile.write("BADINPUT".encode('utf-8'))					
					break
		finally: #Shutting the Server Down raises this exception, so I have it handled
			print("Server is shutting down...")
			server.server_close()
			quit()
		
if __name__ == "__main__":
	#Set up Server
	server = socketserver.TCPServer((serverName, port), MyTCPHandler)
	server.serve_forever()


