#import
import MySQLdb

try:
	#Connection
	conn = MySQLdb.connect(host="localhost",user="username",password="password",db="python")
	cursor = conn.cursor() 
	
	#SQL Queries 1)
	sql = "SELECT * FROM person"
	cursor.execute(sql)
	
	#Fetch Data, if any
	data = cursor.fetchall()
	for d in data:
		print("{} lives on {} in {}".format(d[0],d[1],d[2]))
	
	#SQL Queries 2)
	sql = "SELECT (pname) FROM vehicle WHERE (make='toyota' AND model='camry')"
	cursor.execute(sql)
	
	#Fetch Data, if any
	data = cursor.fetchall()
	for d in data:
		print("{} owns a Toyota Camry".format(d[0]))
	
	#SQL Queries 3)
	sql = "SELECT vehicle.pname FROM vehicle INNER JOIN accident ON vehicle.licplate=accident.licplate WHERE make='toyota' AND model='camry'"
	cursor.execute(sql)
	
	#Fetch Data, if any
	data = cursor.fetchall()
	for d in data:
		print("{}, who owns a Toyota Camry, has an accident on their record.".format(d[0]))
	
	#Close Connection
	conn.close()
except Exception as e:
	print("Connection Failed: " + str(e))
	quit()
	