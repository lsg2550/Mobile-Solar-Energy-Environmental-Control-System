#import
import socketserver

def intTryParse(value): #Cleaner than an if-else or try catch within the block of code
	try:
		return int(value), True
	except ValueError:
		return -1, False

class MyTCPHandler(socketserver.StreamRequestHandler):
	def handle(self):
		try:
			while True:
				#Read Client Input
				self.data = intTryParse(self.rfile.readline().strip())
				if self.data[1] == True:
					#print(self.data)
					self.wfile.write(array[self.data[0]].encode('utf-8'))
				elif self.data == "QUIT":				
					break			
				else:
					self.data = "Wrong Input"
					self.wfile.write(self.data.encode('utf-8'))
		except BrokenPipeError:
			quit()

if __name__ == "__main__":
		#Init
		servername = "127.0.0.1"
		port = 43525 #Random Port
		array = ["Brownsville", "Harlingen", "McAllen", "Edinburg", "Corpus Christi", "Dallas", "Fort Worth", "Austin", "Houston", "El Paso"]		
		
		#Set up Server
		server = socketserver.TCPServer((servername, port), MyTCPHandler)
		server.serve_forever()


