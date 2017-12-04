package connections;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;
import java.sql.Statement;

/**
 *
 * @author Luis
 */
public class MySQL {

    //SQL Connection
    private static Connection connection;
    private static Statement statement;

    public static void init(String username, String password) throws SQLException {
        //Connection connection = DriverManager.getConnection("jdbc:mariadb://localhost:3306/DB?user=username&password=password");        
        connection = DriverManager.getConnection("jdbc:mariadb://localhost:3306/remotesite?user=" + username + "&password=" + password);
        statement = connection.createStatement();
    }

    /**
     * @return the connection
     */
    public static Connection getConnection() {
        return connection;
    }

    /**
     * @return the statement
     */
    public static Statement getStatement() {
        return statement;
    }
}
