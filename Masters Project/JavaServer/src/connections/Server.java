package connections;

import alert.AlertDialog;
import java.io.BufferedInputStream;
import java.io.BufferedOutputStream;
import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.OutputStreamWriter;
import java.net.ServerSocket;
import java.net.Socket;
import java.net.SocketException;
import java.nio.charset.StandardCharsets;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.List;
import javafx.application.Platform;
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
            serverSocket = new ServerSocket(8080);
            serverSocket.setSoTimeout(30000);

            //Debug
            LogSingleton.getInstance().updateLog("Server socket [" + Server.getServerSocket().getLocalSocketAddress() + "] is open...");
        } catch (IOException e) {
            Platform.runLater(() -> {
                AlertDialog.showAlert("Failed to Create Server Socket: " + e.toString());
            });
            e.printStackTrace();
            return;
        }

        while (!this.isInterrupted()) {
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
                AlertDialog.showAlert("Failed to Create Server Socket: " + e.toString());
                e.printStackTrace();
                return;
            }

            //Start Server/Client Communication
            while (isClientConnected) {
                try {
                    String clientInput = null;
                    while ((clientInput = serverReader.readLine()) != null) {
                        switch (clientInput.toUpperCase()) {
                            case "XML":
                                LogSingleton.getInstance().updateLog("Preparing for XML input...");
                                Thread.sleep(1000);

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

                                //TODO: Look back at this, for some reason this while loop won't break properly
                                while ((dataLength = serverInputStream.read(dataBuffer)) > 0) {
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
                                    List<String> xmlNodeList = new ArrayList<String>();
                                    xmlNodeList.add(xmlDoc.getElementsByTagName("temperature").item(0).getTextContent());
                                    xmlNodeList.add(xmlDoc.getElementsByTagName("battery").item(0).getTextContent());
                                    xmlNodeList.add(xmlDoc.getElementsByTagName("solarpanel").item(0).getTextContent());
                                    xmlNodeList.add(xmlDoc.getElementsByTagName("exhaust").item(0).getTextContent());
                                    xmlNodeList.add(xmlDoc.getElementsByTagName("photo").item(0).getTextContent());

                                    for (int i = 0; i < xmlNodeList.size(); i++) {
                                        String currentVital = xmlNodeList.get(i);
                                        MySQL.getStatement().executeUpdate("UPDATE status SET VV = '" + currentVital + "', TS = '" + xmlLogTimeStamp + "' WHERE VID = " + (i + 1) + ";"); //TODO: Create a Prepared Statement in MySQL class
                                        MySQL.getStatement().executeUpdate("INSERT INTO log (VID, TYP, V1, V2, TS) VALUES (" + (i + 1) + ", 'ST', '" + currentVital + "', '', '" + xmlLogTimeStamp + "');"); //TODO: Create a Prepared Statement in MySQL class
                                        System.out.println("UPDATE status SET VV = '" + currentVital + "', TS = '" + xmlLogTimeStamp + "' WHERE VID = " + (i + 1) + ";");
                                        System.out.println("INSERT INTO log (VID, TYP, V1, V2, TS) VALUES (" + (i + 1) + ", 'ST', '" + currentVital + "', '', '" + xmlLogTimeStamp + "');");
                                    }
                                } catch (ParserConfigurationException e) {
                                    e.printStackTrace();
                                } catch (SAXException e) {
                                    e.printStackTrace();
                                } catch (SQLException e) {
                                    e.printStackTrace();
                                }
                                break;
                            case "QUIT":
                                isClientConnected = false;
                                break;
                            default:
                                LogSingleton.getInstance().updateLog(clientInput); //Display ClientInput
                                break;
                        }

                        //Send Server Response
                        serverWriter.write("Message Received.\n");
                        serverWriter.flush();
                    }
                } catch (SocketException e) {
                    isClientConnected = false;
                } catch (IOException e) {
                    e.printStackTrace();
                } catch (InterruptedException e) {
                    e.printStackTrace();
                }
            }

            try {
                LogSingleton.getInstance().updateLog(socket.getInetAddress() + " has disconnected...");
                Thread.sleep(5000);
            } catch (InterruptedException e) {
                Platform.runLater(() -> {
                    AlertDialog.showAlert(e.toString());
                });
                e.printStackTrace();
                return;
            }
        }
    }

    /**
     * @return the serverSocket
     */
    public static ServerSocket getServerSocket() {
        return serverSocket;
    }

    /**
     * @return the socket
     */
    public static Socket getSocket() {
        return socket;
    }

}
