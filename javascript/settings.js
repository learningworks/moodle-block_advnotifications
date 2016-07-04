$(document).ready(function(){
    $('.block_advanced_notifications.dismissible').on('click', function() {
        $(this).slideUp('150', function(){
            $(this).remove();
        });

        //setCookie('dismissedalertblock', $('.userbutton .usertext').text(), 7)
    });
});

//function setCookie(cname, cvalue, exdays) {
//    var d = new Date();
//    d.setTime(d.getTime() + (exdays*24*60*60*1000));
//    var expires = "expires="+ d.toUTCString();
//    document.cookie = cname + "=" + cvalue + "; " + expires;
//}