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

    Craton.prototype.initFunFacts = function() {
        var a = 0;
        $(window).on('scroll', function() {
            var oTop = $('#counter').offset().top - window.innerHeight;
            if (a == 0 && $(window).scrollTop() > oTop) {
                $('.lan_fun_value').each(function() {
                    var $this = $(this),
                        countTo = $this.attr('data-count');
                    $({
                        countNum: $this.text()
                    }).animate({
                        countNum: countTo
                    }, {
                        duration: 2000,
                        easing: 'swing',
                        step: function() {
                            $this.text(Math.floor(this.countNum));
                        },
                        complete: function() {
                            $this.text(this.countNum);
                            //alert('finished');
                        }

                    });
                });
                a = 1;
            }
        });
    },

    Craton.prototype.init = function() {
        this.initFunFacts();
    },
    //init
    $.Craton = new Craton, $.Craton.Constructor = Craton
}(window.jQuery),

//initializing
function($) {
    "use strict";
    $.Craton.init();
}(window.jQuery);