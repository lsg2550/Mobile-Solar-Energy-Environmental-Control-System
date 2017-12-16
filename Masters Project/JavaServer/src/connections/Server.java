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
import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.Date;
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
    private final DateFormat DATE_FORMAT = new SimpleDateFormat("MM/dd/yyyy HH:mm:ss a");
    private final long COOLDOWN = 60000; //1 Min Cooldown
    private long currentTime = System.currentTimeMillis();

    @Override
    public void run() {
        try {
            //Start Server
            serverSocket = new ServerSocket(8080);
            serverSocket.setSoTimeout(30000);
        } catch (IOException e) {
            AlertDialog.showAlert("Failed to Create Server Socket: " + e.toString());
            e.printStackTrace();
            return;
        }

        while (!this.isInterrupted()) {
            try {
                //Debug
                LogSingleton.getInstance().getLog().appendText("\nServer socket [" + Server.getServerSocket().getLocalSocketAddress() + "] is open...");
                System.out.println("Server socket [" + Server.getServerSocket().getLocalSocketAddress() + "] is open...");

                //Wait for Connection
                socket = serverSocket.accept();

                //Set Input/Output Streams
                serverInputStream = new BufferedReader(new InputStreamReader(socket.getInputStream(), StandardCharsets.UTF_8));
                serverOutputStream = new BufferedWriter(new OutputStreamWriter(socket.getOutputStream(), StandardCharsets.UTF_8));

                //Client Connection Status
                isClientConnected = true;

                //Debug
                LogSingleton.getInstance().getLog().appendText("\n" + socket.getLocalAddress() + " has connected...");
                System.out.println(socket.getLocalAddress() + " has connected...");
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
                        String idleMessage = DATE_FORMAT.format(new Date()) + ": Waiting for data...";
                        LogSingleton.getInstance().getLog().appendText("\n" + idleMessage);
                        System.out.println(idleMessage);
                        updateLog("Waiting for data...");
                    }

                    //Process Client Input
                    String clientInput = null;
                    while ((clientInput = serverInputStream.readLine()) != null) {
                        switch (clientInput.toUpperCase()) {
                            case "QUIT":
                                isClientConnected = false;
                                break;
                            case "XML":
                                String systemMessage = DATE_FORMAT.format(new Date()) + ": Preparing for XML input...";
                                System.out.println(systemMessage);
                                updateLog("Preparing for XML input...");
                                break;
                            default:
                                //Display ClientInput
                                String clientMessage = DATE_FORMAT.format(new Date()) + ": " + clientInput;
                                LogSingleton.getInstance().getLog().appendText("\n" + clientMessage);
                                System.out.println(clientMessage);

                                //Send Server Response
                                serverOutputStream.write("Thank you for your message!\n");
                                serverOutputStream.flush();
                                break;
                        }
                    }
                } catch (SocketException e) {
                    System.out.println("Client Timed Out...");
                    updateLog("Client Timed Out...");
                    isClientConnected = false;
                } catch (IOException e) {
                }
            }

            try {
                System.out.println(socket.getInetAddress() + " has disconnected...");
                updateLog(socket.getInetAddress() + " has disconnected...");
                Thread.sleep(5000);
            } catch (InterruptedException ex) {
            }
        }
    }

    /**
     * @param message - message to display on log
     */
    private void updateLog(String message) {
        LogSingleton.getInstance().getLog().appendText(DATE_FORMAT.format(new Date()) + ": " + message + "\n");
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
