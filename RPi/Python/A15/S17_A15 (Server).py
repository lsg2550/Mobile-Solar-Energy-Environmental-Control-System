#import
import socket
import socketserver

class MyTCPHandler(socketserver.StreamRequestHandler):

    def handle(self):
        print("A new user {} has connected.".format(self.client_address[0]))
        
        ############Extra Code
        self.data = self.rfile.readline().strip()
        if (self.data != ""):
                print("User {} has sent ".format(self.client_address[0]) + str(self.data))
        ############Extra Code        

        self.data = "Welcome, {}".format(self.client_address[0])
        self.wfile.write(self.data.encode('utf-8'))

if __name__ == "__main__":
	#Init
	servername = "127.0.0.1"
	port = 43525 #Random Port

	#Set up Server
	server = socketserver.TCPServer((servername, port), MyTCPHandler)
	server.serve_forever()




