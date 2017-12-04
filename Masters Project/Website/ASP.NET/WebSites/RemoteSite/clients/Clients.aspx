﻿<%@ Page Language="C#" AutoEventWireup="true" CodeFile="Clients.aspx.cs" Inherits="clients_Clients" %>

<!DOCTYPE html>
<html>
<head>
    <title>Remote Site - Client Page</title>
</head>
<body>
    <div>
        <h1>Remote Site - Mobile Solar Energy & Environmental Control System</h1>
    </div>
    <div>
        <fieldset><legend>Car Status - {If possible, insert Log (Time,Date) from Code? Otherwise
            add it as a row}</legend>
            <table>
                <tr>
                    <th>Temperature</th>
                    <th>{celsius}/{fahrenheit}</th>
                </tr>
                <tr>
                    <th>Battery Voltage</th>
                    <th>{Voltage}</th>
                </tr>
                <tr>
                    <th>Exhaust</th>
                    <th>{ON/OFF}</th>
                </tr>
                <tr>
                    <th>Solar Panel</th>
                    <th>{Charging/NotCharging}</th>
                </tr>
            </table>
        </fieldset>
        <fieldset><legend>Car Status - 05:00PM - July 24, 2017</legend>
            <table>
                <tr>
                    <th>Temperature</th>
                    <th>20C/68F</th>
                </tr>
                <tr>
                    <th>Battery Voltage</th>
                    <th>12.6V</th>
                </tr>
                <tr>
                    <th>Exhaust</th>
                    <th>ON</th>
                </tr>
                <tr>
                    <th>Solar Panel</th>
                    <th>Not Charging</th>
                </tr>
            </table>
        </fieldset>
    </div>
    <div>
        <form runat="server">
        <asp:Button ID="Button1" runat="server" Text="Back" PostBackUrl="~/Default.aspx" />
        </form>
    </div>
</body>
</html>
