<%@ Page Language="C#" AutoEventWireup="true" CodeFile="Default.aspx.cs" Inherits="_Default" %>

<!DOCTYPE html>
<html>
<head>
    <title>Remote Site - MSE & ECS</title>
</head>
<body>
    <div>
        <h1>Remote Site - Mobile Solar Energy & Environmental Control System</h1>
    </div>
    <div>
        <fieldset><legend>Links</legend>
            <asp:HyperLink ID="HyperLink1" runat="server" NavigateUrl="~/Login.aspx">Login Page</asp:HyperLink>
            <br />
            <asp:HyperLink ID="HyperLink2" runat="server" NavigateUrl="~/clients/clients.aspx">Client Page</asp:HyperLink>
        </fieldset>
    </div>
</body>
</html>
