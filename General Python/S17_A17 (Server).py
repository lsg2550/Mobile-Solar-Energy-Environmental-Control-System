#import
import socketserver

class MyTCPHandler(socketserver.StreamRequestHandler):

	#Init
	serverPassword = "strongpassword"
	secretArray = ["note1", "renewable", "fossil", "2020"]
	secretArrayCount = 0
	isClientConnected = False

	def handle(self):
		if isClientConnected == False:
			self.wfile.write("Please enter the password: ".encode('utf-8'))
		try:
			while True:
				self.data = self.rfile.readline().strip().decode('utf-8') #Read Client Input	
				print(self.data)

				if self.data == serverPassword and isClientConnected == False: #Case-Sensitive
					isClientConnected = True
				elif self.data == "Y" and secretArrayCount < 4 and isClientConnected == True: #Y = More
					self.wfile.write(secretArray[secretArrayCount].encode('utf-8'))
					secretArrayCount += 1
				elif self.data == "Y" and secretArrayCount > 3 and isClientConnected == True: #Y = More
					self.wfile.write("No more content. Closing Session...".encode('utf-8'))
				elif self.data == "QUIT" and isClientConnected == True:
					raise BrokenPipeError		
					print("Raised in QUIT")	
					break			
				else:
					self.wfile.write("Bad Password! Closing Session...".encode('utf-8'))
					print("Raised in Else")
					raise BrokenPipeError					
					break
		except BrokenPipeError: #Shutting the Server Down raises this exception, so I have it handled
			print("Server is shutting down...")
			server.server_close()
			quit()

if __name__ == "__main__":
	#Init
	serverName = "127.0.0.1"
	port = 43525	
		
	#Set up Server
	server = socketserver.TCPServer((serverName, port), MyTCPHandler)
	server.serve_forever()


