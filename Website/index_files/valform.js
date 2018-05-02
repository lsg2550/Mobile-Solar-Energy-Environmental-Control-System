function validateform() {
    var uname = document.getElementById('username').value;
    var pword = document.getElementById('password').value;

    if(uname === "" || pword === "") {
        alert("Please fill all fields.");
        return false;
    }
}