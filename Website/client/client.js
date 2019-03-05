$(document).ready(function () {
    $(window).on('load', function (event) {
        //Prevent default event of changing webpage
        event.preventDefault();

        // console.log("Page Loaded.");
        $.post("clientstatus.php", "", function (x) {
            // console.log(x);
            $(".current-status").html(x);
        });
    });
});