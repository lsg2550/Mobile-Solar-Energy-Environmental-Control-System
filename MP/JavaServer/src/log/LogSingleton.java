package log;

import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.Date;
import javafx.application.Platform;
import javafx.scene.control.TextArea;

/**
 *
 * @author luis
 */
public final class LogSingleton {
    
    private final static DateFormat DATE_FORMAT = new SimpleDateFormat("MM/dd/yyyy HH:mm:ss a");
    private final static LogSingleton INSTANCE = new LogSingleton();
    
    public static LogSingleton getInstance() {
        return INSTANCE;
    }
    
    private TextArea taLog = new TextArea();

    /**
     * @param message - message to display on log
     */
    public void updateLog(String message) {
        Platform.runLater(() -> {
            taLog.appendText(DATE_FORMAT.format(new Date()) + ": " + message + "\n");
        });
    }

    /**
     * @return the taLog
     */
    public TextArea getTaLog() {
        return taLog;
    }
    
}
