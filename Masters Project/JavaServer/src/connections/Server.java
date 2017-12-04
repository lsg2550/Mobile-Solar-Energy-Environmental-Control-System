package connections;

import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.DataInputStream;
import java.io.DataOutputStream;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.io.OutputStreamWriter;
import java.net.ServerSocket;
import java.net.Socket;
import java.net.SocketException;
import java.nio.charset.Charset;
import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.logging.Level;
import java.util.logging.Logger;
import javafx.fxml.FXMLLoader;
import launcher.MainFXMLController;

/**
 *
 * @author Luis
 */
public class Server implements Runnable {

    //Server Creation
    private boolean isClientConnected = false;
    private static ServerSocket serverSocket;
    private static Socket socket;

    //Server Streams
    private BufferedReader serverInputStream;
    private BufferedWriter serverOutputStream;

    //Cooldown
    private final DateFormat DATE_FORMAT = new SimpleDateFormat("MM/dd/yyyy HH:mm:ss a");
    private final long COOLDOWN = 15000; //15 Sec Cooldown
    private long currentTime;

    public Server() {
        try {
            serverSocket = new ServerSocket(8080); //Start Server
        } catch (IOException e) {
            System.out.println("Exception occured: " + e);
        }
    }

    @Override
    public void run() {
        try {
            //Wait for and Establish a Connection
            socket = serverSocket.accept();
            System.out.println("Connection to '" + socket.getInetAddress() + "' has been established...");

            //Set Input/Output Streams
            serverInputStream = new BufferedReader(new InputStreamReader(socket.getInputStream()));
            serverOutputStream = new BufferedWriter(new OutputStreamWriter(socket.getOutputStream()));

            //Client Connection Status
            isClientConnected = true;
        } catch (IOException e) {
            System.out.println("Exception occured: " + e);
            return;
        }

        //Start talking to the server
        currentTime = System.currentTimeMillis();
        while (isClientConnected) {
            try {
                if (currentTime + COOLDOWN <= System.currentTimeMillis()) {
                    currentTime = System.currentTimeMillis();
                    System.out.println(DATE_FORMAT.format(new Date()) + ": Waiting for data...");
                }

                String line = null;
                while ((line = serverInputStream.readLine()) != null) {
                    if (line.toUpperCase().equals("QUIT")) {
                        isClientConnected = false;
                        break;
                    }

                    //MainFXMLController.getLog().appendText(line + "\n");
                    System.out.println(line + "\n");
                }
            } catch (SocketException e) {
                System.out.println("Client Timed Out...");
                isClientConnected = false;
            } catch (IOException e) {
            }
        }

        System.out.println("Connection to '" + socket.getInetAddress() + "' is closed...");
        return; //End Thread
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
