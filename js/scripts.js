(function($){

    $(document).ready(function () {
        console.log("ready");
        $("#mobile-menu").on("click", onMobileMenuClick);

    });
 
    function onMobileMenuClick(e) {
        console.log("click");
        if ($(e.currentTarget).hasClass("active")) {
            $(e.currentTarget).removeClass("active");
            $("#navigation").slideUp(300);
        } else {
            $(e.currentTarget).addClass("active");
            $("#navigation").slideDown(300);
        }
    }

})(jQuery)
