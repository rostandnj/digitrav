/*
------------------------------------------------------------------------
* Template Name    : Carton - Responsive Multipurpose HTML5 Template  * 
* Author           : ThemesBoss                                       *
* Version          : 1.0.0                                            *
* Created          : October 2018                                     *
* File Description : Main Js file of the template                     *
*-----------------------------------------------------------------------
*/

! function($) {
    "use strict";

    var Craton = function() {};

    Craton.prototype.initStickyAddMenu = function() {
        $(window).on('scroll', function() {
            var scroll = $(window).scrollTop();

           /* if (scroll >= 50) {
                $(".sticky").addClass("stickyadd");
            } else {
                $(".sticky").removeClass("stickyadd");
            }*/
        });
    },

    Craton.prototype.initSmoothLink = function() {
       /* $('.nav-item a').on('click', function(event) {
            var $anchor = $(this);
            $('html, body').stop().animate({
                scrollTop: $($anchor.attr('href')).offset().top - 50
            }, 1500, 'easeInOutExpo');
            event.preventDefault();
        });*/
    },

    Craton.prototype.initCollpsehide = function() {
        $(document).on('click', '.navbar-collapse.show', function(e) {
            if ($(e.target).is('a')) {
                $(this).collapse('hide');
            }
        });
    },

    Craton.prototype.initScrollspy = function() {
        /*
        $("#navbarCollapse").scrollspy({
            offset: 70
        });*/
    },

    Craton.prototype.initMFPImage = function() {
        $('.img-zoom').magnificPopup({
            type: 'image',
            closeOnContentClick: true,
            mainClass: 'mfp-fade',
            gallery: {
                enabled: true,
                navigateByImgClick: true,
                preload: [0, 1]
            }
        });
    },

    Craton.prototype.initWorkFilter = function() {
        $(window).on('load', function() {
            var $container = $('.work-filter');
            var $filter = $('#menu-filter');
            $container.isotope({
                filter: '*',
                layoutMode: 'masonry',
                animationOptions: {
                    duration: 750,
                    easing: 'linear'
                }
            });

            $filter.find('a').on("click", function() {
                var selector = $(this).attr('data-filter');
                $filter.find('a').removeClass('active');
                $(this).addClass('active');
                $container.isotope({
                    filter: selector,
                    animationOptions: {
                        animationDuration: 750,
                        easing: 'linear',
                        queue: false,
                    }
                });
                return false;
            });
        });
    },

    Craton.prototype.initClientSlider = function() {
        $("#owl-testi").owlCarousel({
            autoPlay: 10000,
            items: 3,
            itemsDesktop: [1199, 3],
            itemsDesktopSmall: [979, 2]
        });
    },

    Craton.prototype.initteamSlider = function() {
        $("#owl-team").owlCarousel({
            autoPlay: 10000,
            items: 4,
            itemsDesktop: [1199, 3],
            itemsDesktopSmall: [979, 2]
        });
    },

    Craton.prototype.initMFPVideoPlay = function() {
        $('.video_presentation_play').magnificPopup({
            disableOn: 700,
            type: 'iframe',
            mainClass: 'mfp-fade',
            removalDelay: 160,
            preloader: false,
            fixedContentPos: false
        });
    },

    Craton.prototype.initBTT = function() {
        $(window).on('scroll', function() {
            if ($(this).scrollTop() > 100) {
                $('.back_top').fadeIn();
            } else {
                $('.back_top').fadeOut();
            }
        });
        $('.back_top').on('click', function() {
            $("html, body").animate({
                scrollTop: 0
            }, 1000);
            return false;
        });
    },

    Craton.prototype.init = function() {
        this.initStickyAddMenu();
        this.initSmoothLink();
        this.initCollpsehide();
        this.initScrollspy();
        this.initMFPImage();
        this.initWorkFilter();
        this.initClientSlider();
        this.initteamSlider();
        this.initMFPVideoPlay();
        this.initBTT();
    },
    //init
    $.Craton = new Craton, $.Craton.Constructor = Craton
}(window.jQuery),

//initializing
function($) {
    "use strict";
    $.Craton.init();
}(window.jQuery);