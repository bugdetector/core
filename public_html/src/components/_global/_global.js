import {$} from "../jquery/jquery";
import "popper.js";
import "bootstrap/js/src/dropdown";
import "./_global.scss";

$(function ($) {
    // Toggle the side navigation
    $(document).on('click', "#sidebarToggle, #sidebarToggleTop", function (e) {
        $("body").toggleClass("sidebar-toggled");
        $(".sidebar").toggleClass("toggled");
        if ($(".sidebar").hasClass("toggled")) {
            $('.sidebar .collapse').collapse('hide');
        };
    });

    // Close any open menu accordions when window is resized below 768px
    $(window).on("resize", function () {
        if ($(window).width() < 768) {
            $('.sidebar .collapse').collapse('hide');
        };
    });

    // Prevent the content wrapper from scrolling when the fixed side navigation hovered over
    $('body.fixed-nav .sidebar').on('mousewheel DOMMouseScroll wheel', function (e) {
        if ($(window).width() > 768) {
            var e0 = e.originalEvent,
                delta = e0.wheelDelta || -e0.detail;
            this.scrollTop += (delta < 0 ? 1 : -1) * 30;
            e.preventDefault();
        }
    });

    // Scroll to top button appear
    $("#content-wrapper").on('scroll', function () {
        var scrollDistance = $(this).scrollTop();
        if (scrollDistance > 100) {
            $('.scroll-to-top').fadeIn();
        } else {
            $('.scroll-to-top').fadeOut();
        }
    });

    // Smooth scrolling using jQuery easing
    $(document).on('click', 'a.scroll-to-top', function (e) {
        e.preventDefault();
        $("#content-wrapper").animate({
            scrollTop: 0
        }, 1000);
    });

    $(document).on("click", ".clear-cache", function (e) {
        e.preventDefault();
        $.ajax(`${root}/admin/ajax/clearCache`);
    });
})

window._t = function(key, args) {
    if (args) {
        return translations[key].format(args);
    }
    return translations[key];
}

String.prototype.format = function () {
    var a = this, b;
    for (b in arguments) {
        a = a.replace(/%[a-z]/, arguments[b]);
    }
    return a; // Make chainable
};