#import
import socketserver

#Init
serverPassword = "123456"
secretArray = ["note1", "renewable", "fossil", "2020"]
secretArrayCount = 0
isClientConnected = False

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
					self.wfile.write("No more content. Closing Session...".encode('utf-8'))
					raise BrokenPipeError
				elif self.data == "QUIT" and isClientConnected == True:
					self.wfile.write("QUIT".encode('utf-8'))
					raise BrokenPipeError
					break			
				else:
					self.wfile.write("BADINPUT".encode('utf-8'))
					raise BrokenPipeError					
					break
		except BrokenPipeError: #Shutting the Server Down raises this exception, so I have it handled
			print("Server is shutting down...")
			server.server_close()
			quit()
		print("Reached end of function")

if __name__ == "__main__":
	#Init
	serverName = "127.0.0.1"
	port = 43525	
		
	#Set up Server
	server = socketserver.TCPServer((serverName, port), MyTCPHandler)
	server.serve_forever()


