<%@ Page Language="C#" AutoEventWireup="true" CodeFile="Login.aspx.cs" Inherits="Login" %>

<!DOCTYPE html>
<html>
<head>
    <title>Remote Site - Login</title>
</head>
<body>
    <div>
        <h1>Remote Site - Mobile Solar Energy & Environmental Control System</h1>
    </div>
    <div>
        <form id="form1" runat="server">
        <fieldset><legend>Login</legend>
            <asp:Login ID="Login1" runat="server" TitleText=""></asp:Login>
            <asp:ValidationSummary ID="ValidationSummary1" runat="server" ValidationGroup="Login1" />
        </fieldset>
        </form>
    </div>
</body>
</html>
