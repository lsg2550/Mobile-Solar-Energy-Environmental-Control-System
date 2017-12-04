package connections;

import java.io.DataInputStream;
import java.io.DataOutputStream;
import java.io.IOException;
import java.net.ServerSocket;
import java.net.Socket;
import java.net.SocketException;
import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.logging.Level;
import java.util.logging.Logger;

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
    private DataInputStream serverInputStream;
    private DataOutputStream serverOutputStream;

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

            //Set Input/Output Streams
            serverInputStream = new DataInputStream(socket.getInputStream());
            serverOutputStream = new DataOutputStream(socket.getOutputStream());

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

                    //Check if user is still connected
                    System.out.println(DATE_FORMAT.format(new Date())
                            + "\n - Waiting for data..."
                            + "\n - Checking if client is still connected...");
                    socket.setSoTimeout((int) COOLDOWN);
                }
            } catch (SocketException e) {
                System.out.println("Client Timed Out...");
                isClientConnected = false;
            }
        }

        System.out.println("Connection Closed...");
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
