package launcher;

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
import javafx.scene.control.Alert;
import javafx.scene.control.PasswordField;
import javafx.scene.control.TextField;

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
    private void signIn_OnClick(ActionEvent event) {
        try {
            MySQL.init(uField.getText(), pField.getText()); //Connects to MySQL DB
            Parent serverlog = FXMLLoader.load(getClass().getResource("MainFXMLDocument.fxml"));
            Scene scene = new Scene(serverlog);
            Launcher.getStage().setScene(scene);
        } catch (SQLException | IOException e) {
            Alert a = new Alert(Alert.AlertType.ERROR);
            a.setContentText(e.toString());
            a.showAndWait();
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
