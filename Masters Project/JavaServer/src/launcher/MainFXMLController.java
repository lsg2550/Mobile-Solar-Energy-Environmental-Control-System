package launcher;

import connections.MySQL;
import connections.Server;
import java.net.URL;
import java.util.ResourceBundle;
import javafx.fxml.FXML;
import javafx.fxml.Initializable;
import javafx.scene.control.TextArea;

/**
 *
 * @author Luis
 */
public class MainFXMLController implements Initializable {

    @FXML
    private TextArea log = new TextArea();

    private void onLoad() {
        try {
            if (!MySQL.getConnection().isClosed()) {
                log.appendText("Server is connected to the MySQL Database...\n");
            }

            Server server = new Server();
            Thread serverThread = new Thread(server);
            serverThread.start();

            if (!Server.getServerSocket().isClosed()) {
                log.appendText("Server socket[" + Server.getServerSocket().getLocalPort() + "] is open and awaiting connections...\n");
            }
        } catch (Exception e) { //Likely IO/SQL/Null Exceptions
            log.appendText("Exception occured: " + e);
        }

    }

    @Override
    public void initialize(URL url, ResourceBundle rb) {
        // TODO
        onLoad();
    }

}
