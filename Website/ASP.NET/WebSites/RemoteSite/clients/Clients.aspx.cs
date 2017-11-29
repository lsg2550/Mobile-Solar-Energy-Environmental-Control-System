using System;

public partial class clients_Clients : System.Web.UI.Page {
    protected void Page_Load(object sender, EventArgs e) {

    }

    private double celsius2Fahrenheit(double celsius) {
        return ((9 / 5) * celsius) + 32;
    }

    private double fahrenheit2Celsius(double fahrenheit) {
        return (5 / 9) * (fahrenheit - 32);
    }
}