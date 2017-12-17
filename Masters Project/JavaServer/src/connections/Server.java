package connections;

import alert.AlertDialog;
import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.OutputStreamWriter;
import java.net.ServerSocket;
import java.net.Socket;
import java.net.SocketException;
import java.nio.charset.StandardCharsets;
import javafx.application.Platform;
import log.LogSingleton;

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
    private BufferedReader serverInputStream;
    private BufferedWriter serverOutputStream;

    //Cooldown
    private final long COOLDOWN = 60000; //1 Min Cooldown
    private long currentTime = System.currentTimeMillis();

    @Override
    public void run() {
        try {
            //Start Server
            serverSocket = new ServerSocket(8080);
            serverSocket.setSoTimeout(30000);

            //Debug
            LogSingleton.getInstance().updateLog("Server socket [" + Server.getServerSocket().getLocalSocketAddress() + "] is open...");
        } catch (IOException e) {
            AlertDialog.showAlert("Failed to Create Server Socket: " + e.toString());
            e.printStackTrace();
            return;
        }

        while (!this.isInterrupted()) {
            try {
                //Wait for Connection
                socket = serverSocket.accept();

                //Set Input/Output Streams
                serverInputStream = new BufferedReader(new InputStreamReader(socket.getInputStream(), StandardCharsets.UTF_8));
                serverOutputStream = new BufferedWriter(new OutputStreamWriter(socket.getOutputStream(), StandardCharsets.UTF_8));

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
                    //Debug
                    if (currentTime + COOLDOWN <= System.currentTimeMillis()) {
                        currentTime = System.currentTimeMillis();
                        LogSingleton.getInstance().updateLog("Waiting for data...");
                    }

                    //Process Client Input
                    String clientInput = null;
                    while ((clientInput = serverInputStream.readLine()) != null) {
                        switch (clientInput.toUpperCase()) {
                            case "QUIT":
                                isClientConnected = false;
                                break;
                            case "XML":
                                LogSingleton.getInstance().updateLog("Preparing for XML input...");
                                Thread.sleep(1000);
                                LogSingleton.getInstance().updateLog(serverInputStream.readLine());
                                //MySQL.getStatement().executeUpdate(clientInput);
                                break;
                            default:
                                //Display ClientInput
                                LogSingleton.getInstance().updateLog(clientInput);
                                break;
                        }

                        //Send Server Response
                        serverOutputStream.write("Thank you for your message!\n");
                        serverOutputStream.flush();
                    }
                } catch (SocketException e) {
                    LogSingleton.getInstance().updateLog("Client Timed Out...");
                    isClientConnected = false;
                } catch (IOException e) {
                    System.out.println(e.toString());
                } catch (InterruptedException e) {
                    System.out.println(e.toString());
                }
            }

            try {
                LogSingleton.getInstance().updateLog(socket.getInetAddress() + " has disconnected...");
                Thread.sleep(5000);
            } catch (InterruptedException e) {
                AlertDialog.showAlert(e.toString());
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
