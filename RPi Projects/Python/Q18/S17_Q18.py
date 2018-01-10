#import
import MySQLdb

try:
	#Connection
	conn = MySQLdb.connect(host="localhost",user="username",password="password",db="python")
	cursor = conn.cursor() 
	
	#SQL Queries 
	
	#Person Table
	sql = "CREATE TABLE person (pname VARCHAR(30) NOT NULL, street VARCHAR(50) NOT NULL, city VARCHAR(50) NOT NULL, Primary Key (pname))"
	cursor.execute(sql)

	#Vehicle Table
	sql = "CREATE TABLE vehicle (year VARCHAR(4) NOT NULL, make VARCHAR(30) NOT NULL, model VARCHAR(30) NOT NULL, cost INT NOT NULL," \
		+ "licplate VARCHAR(7) NOT NULL, pname VARCHAR(30) NOT NULL, Primary Key (licplate,pname))"
	cursor.execute(sql)
	
	#Accident Table
	sql = "CREATE TABLE accident (accnum INT NOT NULL, licplate VARCHAR(7) NOT NULL, accdate date NOT NULL, pname VARCHAR(30) NOT NULL," \
		+ "Primary Key (accnum,licplate))"
	cursor.execute(sql)

	#Add Content to Person Table
	sql = "INSERT INTO person VALUES ('marisol','zenith','harlingen'), ('dolly','pstreet','brownsville'), ('zapata','media','edinburg')," \
		+ "('sunny','zenith','Harlingen'), ('gloria','pstreet','brownsville'), ('puente','winder','edinburg'), ('ben','zed','edinburg')"
	cursor.execute(sql)	
	
	#Add Content to Accident Table
	sql = "INSERT INTO accident VALUES (101, 'n123', '2012/07/15', 'sunny'), (102, 'h123', '2014/04/04', 'sunny'), (103, 'b123', '2014/01/25', 'zapata')," \
		+ "(104, 'b123', '2013/02/16', 'sunny'), (105, 't123', '2012/06/06', 'sunny'), (106, 'k123', '2011/09/17', 'dolly'), (107, 'k123', '2013/08/24', 'dolly')," \
		+ "(108, 'n123', '2010/12/12', 'gloria'), (109, 'b123', '2010/11/05', 'zapata'), (110, 't123', '2010/03/03', 'sunny')"
	cursor.execute(sql)
	
	#Add Content to Vehicle Table
	sql = "INSERT INTO vehicle VALUES (2005, 'toyota', 'camry', 25000, 't123', 'marisol'), (1996, 'jeep', 'wrangler', 23000, 'm123', 'marisol')," \
		+ "(1997, 'suzuki', 'samurai', 25000, 'k123', 'dolly'), (2014, 'jeep', 'wrangler', 29000, 'f123', 'zapata'), (2006, 'bmw', '318i', 35000, 'b123', 'zapata')," \
		+ "(2013, 'ford', 150, 31000, 'h123', 'dolly'), (2005, 'toyota', 'camry', 25000, 't123', 'sunny'), (1966, 'ford', 'mustang', 18000, 'n123', 'gloria')," \
		+ "(2015, 'dodge', 'journey', 28000, 'j123', 'puente'), (2010, 'toyota', 'tundra', 39000, 'y123', 'ben')"
	cursor.execute(sql)
	
	#Commit All Changes
	conn.commit()
	
	#Close Connection
	conn.close()
except Exception as e:
	print("Connection Failed: " + str(e))
	quit()