#import
import MySQLdb
import socketserver

#Global
sql = ""

def recordExists(record):
	try:
		if record[0]:
			return True
	except IndexError:
		return False

class MyTCPHandler(socketserver.StreamRequestHandler):

	def handle(self):
		global sql
		
		try:
			while True:
				self.data = self.rfile.readline().strip().decode('utf-8') #Read Client Input	
				print(self.data)	

				if self.data.upper() == "QUIT":
					self.wfile.write("QUIT".encode('utf-8'))
					break
				else:
						#SQL Queries
						sql_person = "SELECT * FROM person WHERE pname='"+self.data+"'"
						cursor.execute(sql_person)
						personRecord = cursor.fetchall()
					
						sql_vehicle= "SELECT * FROM vehicle WHERE pname='"+self.data+"'"
						cursor.execute(sql_vehicle)
						vehicleRecord = cursor.fetchall()
					
						sql_accident="SELECT * FROM accident WHERE pname='"+self.data+"'"
						cursor.execute(sql_accident)
						accidentRecord = cursor.fetchall()
							
						try:
							if personRecord[0]:
								toClient = "Person: {}\n".format(personRecord)
							
								if recordExists(vehicleRecord) == True:
									toClient += "Vehicles Owned: {}\n".format(vehicleRecord)
								if recordExists(accidentRecord) == True:
									toClient += "Accidents On Record: {}\n".format(accidentRecord)
								
								toClient += "//End of File"
								self.wfile.write(toClient.encode('utf-8'))
						except IndexError:
							self.wfile.write("UNKNOWN".encode('utf-8'))
		except Exception as e:
			print("Exception: " + str(e))
		finally: 
			print("Server is shutting down...")
			server.server_close()
			conn.close()
			quit()

if __name__ == "__main__":
	#Init - Server Information
	serverName = "127.0.0.1"
	port = 43523
	
	try:
		#Connection
		conn = MySQLdb.connect(host="localhost",user="username",password="password",db="python")
		cursor = conn.cursor() 
	
		#Set up Server
		server = socketserver.TCPServer((serverName, port), MyTCPHandler)
		server.serve_forever()
	except Exception as e:
		print("Connection Failed: " + str(e))
		quit()
		






	
	
