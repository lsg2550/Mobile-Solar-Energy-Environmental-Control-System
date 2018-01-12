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
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
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
    private static ServerSocket serverSocket = null;
    private static Socket socket = null;

    //Local XML File
    private int xmlFileCount = 0;
    private String xmlFileName = "log";
    private File xmlFile = new File("docs/" + xmlFileName + xmlFileCount + ".xml");

    //Date for MySQL Datetime
    private final static SimpleDateFormat SIMPLE_DATE_FORMAT = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");

    @Override

    public void run() {
        try {
            //Start Server
            serverSocket = new ServerSocket(8080, 5, InetAddress.getByName("192.168.2.29"));

            //Debug
            LogSingleton.getInstance().updateLog("Server socket [" + serverSocket.getLocalSocketAddress() + "] is open...");
        } catch (IOException e) {
            LogSingleton.getInstance().updateLog("Failed to Create Server Socket: " + e.toString());
            e.printStackTrace();
            return;
        }

        while (!this.isInterrupted()) {
            try {
                //Wait for Connection
                socket = serverSocket.accept();
            } catch (IOException ex) {
            }

            new Thread(() -> {
                //Init Socket Streams
                boolean isClientConnected = true;
                BufferedInputStream serverInputStream = null;
                BufferedOutputStream serverOutputStream = null;
                FileOutputStream serverFileOutputStream = null;
                BufferedReader serverReader = null;
                BufferedWriter serverWriter = null;

                try {
                    //Set Input/Output Streams
                    serverInputStream = new BufferedInputStream(socket.getInputStream());
                    //serverOutputStream = new BufferedOutputStream(socket.getOutputStream());
                    serverReader = new BufferedReader(new InputStreamReader(socket.getInputStream(), StandardCharsets.UTF_8));
                    serverWriter = new BufferedWriter(new OutputStreamWriter(socket.getOutputStream(), StandardCharsets.UTF_8));

                    //Debug
                    LogSingleton.getInstance().updateLog(socket.getInetAddress() + " has connected...");
                } catch (IOException e) {
                    LogSingleton.getInstance().updateLog("Failed to Create Server Socket: " + e.toString());
                    e.printStackTrace();
                    return;
                }

                /* Start Server/Client Communication */
                while (isClientConnected) {
                    try {
                        //Client Variables
                        String clientName = null; //Client that Connected
                        String clientInput = null; //Client Input
                        String clientOwnerName = null; //Client Owner (Used by Raspberry Pi)
                        while (((clientInput = serverReader.readLine()) != null)) {
                            switch (clientInput.toUpperCase()) {
                                case "XML": //Receive XML - Only used by raspberry pi
                                    LogSingleton.getInstance().updateLog("Preparing for XML input...");

                                    //Check if XML File Exists
                                    if (!xmlFile.exists()) {
                                        xmlFile.createNewFile();
                                    } else {
                                        while (xmlFile.exists()) { //In case the server resets, it'll count back up to the current log count
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
                                            String updateXML = "UPDATE status SET VV = '" + currentVital + "', TS = '" + xmlLogTimeStamp + "' WHERE VID = " + (i + 1) + ";";
                                            String insertXML = "INSERT INTO log (VID, TYP, USR, V1, V2, TS) VALUES (" + (i + 1) + ", 'ST', '" + clientOwnerName + "', '" + currentVital + "', '', '" + xmlLogTimeStamp + "');";

                                            //Execute Queries
                                            MySQL.getStatement().executeUpdate(updateXML);
                                            MySQL.getStatement().executeUpdate(insertXML);

                                            //Debug
                                            LogSingleton.getInstance().updateLog(updateXML);
                                            LogSingleton.getInstance().updateLog(insertXML);
                                        }
                                    } catch (ParserConfigurationException | SAXException e) {
                                        e.printStackTrace();
                                    }
                                    break;
                                case "REQUEST": //Retreive Data
                                    LogSingleton.getInstance().updateLog("Preparing for Data Retrieval...");
                                    serverOutputStream = new BufferedOutputStream(socket.getOutputStream());

                                    //Retreive from MySQL DB
                                    ResultSet resultREQUEST = MySQL.getStatement().executeQuery("SELECT v.VN ,s.VV, s.TS FROM status s JOIN vitals v ON s.VID = v.VID ORDER BY s.VID;");
                                    String resultDebug = "",
                                     resultData = "";

                                    //Select from CurrentStatus
                                    serverOutputStream.write("CURRENT".getBytes());
                                    serverOutputStream.flush();
                                    Thread.sleep(2500); //
                                    while (resultREQUEST.next()) {
                                        //For server output
                                        resultDebug += "[" + resultREQUEST.getString("VN") + ","
                                                + resultREQUEST.getString("VV") + ","
                                                + resultREQUEST.getString("TS") + "]\n";
                                        //For client output
                                        resultData = "[" + resultREQUEST.getString("VN") + ","
                                                + resultREQUEST.getString("VV") + ","
                                                + resultREQUEST.getString("TS") + "]";

                                        //Send Data
                                        serverOutputStream.write(resultData.getBytes());
                                        serverOutputStream.flush();
                                        Thread.sleep(2500);
                                    }
                                    serverOutputStream.write("CURRENTEND".getBytes());
                                    serverOutputStream.flush();
                                    Thread.sleep(2500);

                                    //Select from Log
                                    resultREQUEST = MySQL.getStatement().executeQuery("SELECT l.NUM, v.VN, l.TYP, l.V1, l.V2, l.TS FROM log l JOIN vitals v ON l.VID = v.VID ORDER BY l.NUM;"); //l.VID = v.VID is preventing log to show login attempts
                                    serverOutputStream.write("LOG".getBytes());
                                    serverOutputStream.flush();
                                    Thread.sleep(2500); // 
                                    while (resultREQUEST.next()) {
                                        //For server output
                                        resultDebug += "[" + resultREQUEST.getString("NUM") + ","
                                                + resultREQUEST.getString("VN") + ","
                                                + resultREQUEST.getString("TYP") + ","
                                                + resultREQUEST.getString("V1") + ","
                                                + resultREQUEST.getString("V2") + ","
                                                + resultREQUEST.getString("TS") + "]\n";

                                        //For client output
                                        resultData = "[" + resultREQUEST.getString("NUM") + ","
                                                + resultREQUEST.getString("VN") + ","
                                                + resultREQUEST.getString("TYP") + ","
                                                + resultREQUEST.getString("V1") + ","
                                                + resultREQUEST.getString("V2") + ","
                                                + resultREQUEST.getString("TS") + "]";

                                        //Send Data
                                        serverOutputStream.write(resultData.getBytes());
                                        serverOutputStream.flush();
                                        Thread.sleep(2500);
                                    }
                                    serverOutputStream.write("LOGEND".getBytes());
                                    serverOutputStream.flush();
                                    serverOutputStream.close();

                                    //Debug & Close Stream
                                    LogSingleton.getInstance().updateLog(resultDebug.getBytes().length + " bytes of data sent...");
                                    break;
                                case "SIGNIN":
                                    LogSingleton.getInstance().updateLog("Preparing for Sign-In Credentials...");

                                    //Receive Credentials
                                    LogSingleton.getInstance().updateLog("Receiving Credentials...");
                                    String[] userpass = new String[2]; //[0] = username; [1] = password;
                                    for (int i = 0; i < userpass.length; i++) {
                                        userpass[i] = serverReader.readLine();
                                        LogSingleton.getInstance().updateLog(userpass[i]); //Debug
                                    }

                                    ResultSet resultSIGNIN = MySQL.getStatement().executeQuery("SELECT username, password FROM users;");
                                    boolean isCredentialsCorrect = false;
                                    String insertSIGNIN = null;
                                    while (resultSIGNIN.next()) {
                                        if (userpass[0].equals(resultSIGNIN.getString("username")) && userpass[1].equals(resultSIGNIN.getString("password"))) {
                                            //Notify Client
                                            serverWriter.write("ACCEPT");

                                            //Update Database
                                            insertSIGNIN = "INSERT INTO log (VID, TYP, USR, V1, V2, TS) "
                                                    + "VALUES (NULL, 'LA', '"
                                                    + userpass[0] + "', "
                                                    + "'ACC', NULL, '"
                                                    + SIMPLE_DATE_FORMAT.format(new Date()) + "');";
                                            MySQL.getStatement().executeUpdate(insertSIGNIN);

                                            //Debug
                                            LogSingleton.getInstance().updateLog("ACCEPT");
                                            isCredentialsCorrect = true;
                                            clientName = userpass[0];
                                            ResultSet resultOwner = MySQL.getStatement().executeQuery("SELECT owner FROM users WHERE username='" + clientName + "'");
                                            resultOwner.next();
                                            clientOwnerName = resultOwner.getString(1);
                                            break;
                                        }
                                    }

                                    if (!isCredentialsCorrect) {
                                        //Notify Client
                                        serverWriter.write("REJECT");

                                        //Update Database
                                        insertSIGNIN = "INSERT INTO log (VID, TYP, USR, V1, V2, TS) "
                                                + "VALUES (NULL, 'LA', '"
                                                + userpass[0] + "', "
                                                + "'REJ', '', '"
                                                + SIMPLE_DATE_FORMAT.format(new Date()) + "');";
                                        MySQL.getStatement().executeUpdate(insertSIGNIN);

                                        //Debug
                                        LogSingleton.getInstance().updateLog("REJECT");
                                    }

                                    //Debug
                                    serverWriter.flush();
                                    LogSingleton.getInstance().updateLog("Done Verifying Credentials...");
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
                    Thread.sleep(2500); //2.5 sec
                } catch (InterruptedException e) {
                    LogSingleton.getInstance().updateLog("Server thread was interrupted: " + e.toString());
                    e.printStackTrace();
                    return;
                }
            }).start();
        }
    }
}
