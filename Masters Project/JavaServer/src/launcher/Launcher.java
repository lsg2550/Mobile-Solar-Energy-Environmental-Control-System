package launcher;

import javafx.application.Application;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.stage.Stage;

/**
 *
 * @author Luis
 */
public class Launcher extends Application {

    private static Stage stage = new Stage();

    @Override
    public void start(Stage stage) throws Exception {
        //Load GUI
        Parent root = FXMLLoader.load(getClass().getResource("SignIn.fxml"));
        Scene scene = new Scene(root);
        this.stage.setTitle("Administrative Log");
        this.stage.setResizable(false);
        this.stage.setScene(scene);
        this.stage.show();
        this.stage.setOnCloseRequest(e -> {
            System.exit(0);
        });
    }

    /**
     * @param args the command line arguments
     */
    public static void main(String[] args) {
        launch(args);
    }

    /**
     * @return the stage
     */
    public static Stage getStage() {
        return stage;
    }

}
