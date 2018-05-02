package alert;

import javafx.application.Platform;
import javafx.scene.control.Alert;

/**
 *
 * @author luis
 */
public final class AlertDialog extends Alert {

    private final static AlertDialog alertDialog = new AlertDialog(AlertType.ERROR);

    private AlertDialog(AlertType at) {
        super(at);
    }

    public static void showAlert(String alertMessage) {
        alertDialog.setContentText(alertMessage);
        alertDialog.showAndWait();
    }
}
