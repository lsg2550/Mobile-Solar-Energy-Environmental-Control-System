package log;

import connections.MySQL;
import connections.Server;
import java.net.URL;
import java.sql.SQLException;
import java.util.ResourceBundle;
import javafx.fxml.FXML;
import javafx.fxml.Initializable;
import javafx.scene.control.TextArea;

/**
 *
 * @author Luis
 */
public class LogController implements Initializable {

    @FXML
    private TextArea taLog;

    private void onLoad() {
        try {
            if (!MySQL.getConnection().isClosed()) {
                LogSingleton.getInstance().updateLog("Server is connected to the MySQL Database...");
            }

            //Load Server
            Server server = new Server();
            server.start();
        } catch (SQLException e) {
            LogSingleton.getInstance().updateLog("Exception occured: " + e.toString());
            //e.printStackTrace();
        }
    }

    @Override
    public void initialize(URL url, ResourceBundle rb) {
        taLog.textProperty().bind(LogSingleton.getInstance().getTaLog().textProperty());
        taLog.positionCaret(taLog.textProperty().length().get());
        onLoad();
    }
}
