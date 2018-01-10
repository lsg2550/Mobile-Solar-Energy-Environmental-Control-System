package signin;

import alert.AlertDialog;
import connections.MySQL;
import java.io.IOException;
import java.net.URL;
import java.sql.SQLException;
import java.util.ResourceBundle;
import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.fxml.Initializable;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.PasswordField;
import javafx.scene.control.TextField;
import launcher.Launcher;

/**
 * FXML Controller class
 *
 * @author luis
 */
public class SignInController implements Initializable {

    @FXML
    private PasswordField pField;
    @FXML
    private TextField uField;

    @FXML
    private void signinOnClick(ActionEvent event) {
        try {
            MySQL.init(uField.getText(), pField.getText()); //Connects to MySQL DB
            Parent serverLog = FXMLLoader.load(getClass().getResource("/log/LogDocument.fxml"));
            Scene scene = new Scene(serverLog);
            Launcher.getStage().setScene(scene);
        } catch (SQLException | IOException e) {
            AlertDialog.showAlert(e.toString());
            //e.printStackTrace();
        }
    }

    /**
     * Initializes the controller class.
     */
    @Override
    public void initialize(URL url, ResourceBundle rb) {
        // TODO
        pField.setText("");
        uField.setText("");
    }

}
