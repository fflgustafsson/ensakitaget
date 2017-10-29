(function($){

    $(document).ready(function () {

        // Mobile nav
        $("#nav__toggle").on("click", function() {
            $(".nav__mobile").fadeToggle();
            $(".js-nav-overlay").toggle();
            $("body").css("overflow-y", "hidden");
        });

        $(".nav__mobile__close, .js-nav-overlay").on("click", function() {
            $(".nav__mobile").fadeToggle();
            $(".js-nav-overlay").toggle();
            $("body").css("overflow-y", "auto");
        });

        if ($(window).width() > 768) {
           $(".nav__mobile, .js-nav-overlay").hide();
           $("body").css("overflow-y", "auto");
        }

        $(window).resize(function() {
            if($(window).width() > 768) {
                $(".nav__mobile, .js-nav-overlay").hide();
                $("body").css("overflow-y", "auto");
            }
        });

        // Categoryfix.

        if ($('.inspirationWrap__menu .current-menu-item').length === 0) {
            $('.inspirationWrap__categoryTitle').show();
        }

    });

})(jQuery)
