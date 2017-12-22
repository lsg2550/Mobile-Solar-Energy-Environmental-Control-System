package connections;

import java.io.BufferedInputStream;
import java.io.BufferedOutputStream;
import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.OutputStreamWriter;
import java.net.InetAddress;
import java.net.ServerSocket;
import java.net.Socket;
import java.net.SocketException;
import java.nio.charset.StandardCharsets;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.List;
import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;
import javax.xml.parsers.ParserConfigurationException;
import log.LogSingleton;
import org.w3c.dom.Document;
import org.xml.sax.SAXException;

/**
 *
 * @author Luis
 */
public final class Server extends Thread {

    //Server Creation
    private boolean isClientConnected = false;
    private static ServerSocket serverSocket;
    private static Socket socket;

    //Server Streams
    private BufferedInputStream serverInputStream;
    private BufferedOutputStream serverOutputStream;
    private FileOutputStream serverFileOutputStream;
    private BufferedReader serverReader;
    private BufferedWriter serverWriter;

    //Local XML File
    private int xmlFileCount = 0;
    private String xmlFileName = "log";
    private File xmlFile = new File("docs/" + xmlFileName + xmlFileCount + ".xml");

    @Override
    public void run() {
        try {
            //Start Server
            serverSocket = new ServerSocket(8080, 5, InetAddress.getByName("127.0.0.1"));
            //Debug
            LogSingleton.getInstance().updateLog("Server socket [" + serverSocket.getLocalSocketAddress() + "] is open...");
        } catch (IOException e) {
            LogSingleton.getInstance().updateLog("Failed to Create Server Socket: " + e.toString());
            e.printStackTrace();
            return;
        }

        while (!this.isInterrupted()) {
            /* Wait for a connection */
            try {
                //Wait for Connection
                socket = serverSocket.accept();

                //Set Input/Output Streams
                serverInputStream = new BufferedInputStream(socket.getInputStream());
                //serverOutputStream = new BufferedOutputStream(socket.getOutputStream());
                serverReader = new BufferedReader(new InputStreamReader(socket.getInputStream(), StandardCharsets.UTF_8));
                serverWriter = new BufferedWriter(new OutputStreamWriter(socket.getOutputStream(), StandardCharsets.UTF_8));

                //Client Connection Status
                isClientConnected = true;

                //Debug
                LogSingleton.getInstance().updateLog(socket.getLocalAddress() + " has connected...");
            } catch (IOException e) {
                LogSingleton.getInstance().updateLog("Failed to Create Server Socket: " + e.toString());
                e.printStackTrace();
                return;
            }

            /* Start Server/Client Communication */
            while (isClientConnected) {
                try {
                    String clientInput = null;
                    while (((clientInput = serverReader.readLine()) != null)) {
                        switch (clientInput.toUpperCase()) {
                            case "XML": //Receive XML
                                LogSingleton.getInstance().updateLog("Preparing for XML input...");
                                Thread.sleep(1000); //1 sec

                                if (!xmlFile.exists()) {
                                    xmlFile.createNewFile();
                                } else {
                                    while (xmlFile.exists()) {
                                        xmlFileCount++;
                                        xmlFile = new File("docs/" + xmlFileName + xmlFileCount + ".xml");
                                    }
                                    xmlFile.createNewFile();
                                }

                                //Init Output Streams & Data Array
                                serverFileOutputStream = new FileOutputStream(xmlFile);
                                serverOutputStream = new BufferedOutputStream(serverFileOutputStream);
                                byte[] dataBuffer = new byte[1024];
                                int dataLength = 0;

                                LogSingleton.getInstance().updateLog("Receiving File...");
                                while ((dataLength = serverInputStream.read(dataBuffer)) > 0) {
                                    //TODO: Look back at this, for some reason this while loop won't break properly
                                    serverOutputStream.write(dataBuffer, 0, dataLength);
                                    serverFileOutputStream.flush();
                                    serverOutputStream.flush();

                                    //System.out.println(dataLength);
                                    break;
                                }

                                //Close Streams
                                LogSingleton.getInstance().updateLog("File Received.");
                                serverFileOutputStream.close();
                                serverOutputStream.close();

                                //Read from XML and Process into MySQL Query
                                try {
                                    DocumentBuilderFactory xmlFactory = DocumentBuilderFactory.newInstance();
                                    DocumentBuilder xmlBuilder = xmlFactory.newDocumentBuilder();
                                    Document xmlDoc = xmlBuilder.parse(xmlFile);

                                    String xmlLogTimeStamp = xmlDoc.getElementsByTagName("log").item(0).getTextContent();
                                    List<String> xmlNodeList = new ArrayList<>();
                                    xmlNodeList.add(xmlDoc.getElementsByTagName("temperature").item(0).getTextContent());
                                    xmlNodeList.add(xmlDoc.getElementsByTagName("battery").item(0).getTextContent());
                                    xmlNodeList.add(xmlDoc.getElementsByTagName("solarpanel").item(0).getTextContent());
                                    xmlNodeList.add(xmlDoc.getElementsByTagName("exhaust").item(0).getTextContent());
                                    xmlNodeList.add(xmlDoc.getElementsByTagName("photo").item(0).getTextContent());

                                    //TODO: Create Prepared Statements in MySQL class
                                    for (int i = 0; i < xmlNodeList.size(); i++) {
                                        String currentVital = xmlNodeList.get(i);
                                        String updateQuery = "UPDATE status SET VV = '" + currentVital + "', TS = '" + xmlLogTimeStamp + "' WHERE VID = " + (i + 1) + ";";
                                        String insertQuery = "INSERT INTO log (VID, TYP, V1, V2, TS) VALUES (" + (i + 1) + ", 'ST', '" + currentVital + "', '', '" + xmlLogTimeStamp + "');";

                                        //Execute Queries
                                        MySQL.getStatement().executeUpdate(updateQuery);
                                        MySQL.getStatement().executeUpdate(insertQuery);

                                        //Debug
                                        LogSingleton.getInstance().updateLog(updateQuery);
                                        LogSingleton.getInstance().updateLog(insertQuery);
                                    }
                                } catch (ParserConfigurationException | SAXException e) {
                                    e.printStackTrace();
                                }
                                break;
                            case "REQUEST": //Retreive Data
                                LogSingleton.getInstance().updateLog("Preparing for Data Retrieval...");
                                serverOutputStream = new BufferedOutputStream(socket.getOutputStream());

                                //Retreive from MySQL DB
                                ResultSet result = MySQL.getStatement().executeQuery("SELECT v.VN ,s.VV, s.TS "
                                        + "FROM status s JOIN vitals v ON s.VID = v.VID ORDER BY s.VID;");
                                String resultDebug = "",
                                 resultData = "";

                                //Select from CurrentStatus
                                serverOutputStream.write("CURRENT".getBytes());
                                serverOutputStream.flush();
                                Thread.sleep(5000);
                                while (result.next()) {
                                    //For server output
                                    resultDebug += "[" + result.getString("VN") + "," + result.getString("VV") + "," + result.getString("TS") + "]\n";
                                    //For client output
                                    resultData = "[" + result.getString("VN") + "," + result.getString("VV") + "," + result.getString("TS") + "]";

                                    //Send Data
                                    serverOutputStream.write(resultData.getBytes());
                                    serverOutputStream.flush();
                                    Thread.sleep(2500);
                                }
                                serverOutputStream.write("CURRENTEND".getBytes());
                                serverOutputStream.flush();
                                Thread.sleep(2500);

                                //Select from Log
                                result = MySQL.getStatement().executeQuery("SELECT l.NUM, v.VN, l.TYP, l.V1, l.V2, l.TS "
                                        + "FROM log l JOIN vitals v ON l.VID = v.VID ORDER BY l.NUM;");
                                serverOutputStream.write("LOG".getBytes());
                                serverOutputStream.flush();
                                Thread.sleep(5000);
                                while (result.next()) {
                                    //For server output
                                    resultDebug += "[" + result.getString("NUM") + "," + result.getString("VN") + "," + result.getString("TYP") + ","
                                            + result.getString("V1") + "," + result.getString("V2") + "," + result.getString("TS") + "]\n";
                                    //For client output
                                    resultData = "[" + result.getString("NUM") + "," + result.getString("VN") + "," + result.getString("TYP") + ","
                                            + result.getString("V1") + "," + result.getString("V2") + "," + result.getString("TS") + "]";

                                    //Send Data
                                    serverOutputStream.write(resultData.getBytes());
                                    serverOutputStream.flush();
                                    Thread.sleep(2500);
                                }
                                serverOutputStream.write("LOGEND".getBytes());
                                serverOutputStream.flush();
                                Thread.sleep(2500);

                                //Debug & Close Stream
                                LogSingleton.getInstance().updateLog(resultDebug.getBytes().length + " bytes of data sent...");
                                serverOutputStream.close();
                                break;
                            case "QUIT": //Quit
                                isClientConnected = false;
                                break;
                            default:
                                LogSingleton.getInstance().updateLog(clientInput);
                                serverWriter.write("Message Received!\n");
                                serverWriter.flush();
                                break;
                        }
                    }
                } catch (SocketException e) { //Client Disconnects
                    isClientConnected = false;
                    //e.printStackTrace();
                } catch (IOException | SQLException | InterruptedException e) {
                    e.printStackTrace();
                }
            }

            try {
                LogSingleton.getInstance().updateLog(socket.getInetAddress() + " has disconnected...");
                Thread.sleep(5000); //5 sec
            } catch (InterruptedException e) {
                LogSingleton.getInstance().updateLog("Server thread was interrupted: " + e.toString());
                e.printStackTrace();
                return;
            }
        }
    }
}
