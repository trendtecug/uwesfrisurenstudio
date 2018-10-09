

!function($) {

    var defaults = {
        sectionContainer: "section"
    };

$.fn.onepage_scroll = function(options) {
    var settings = $.extend({}, defaults, options);

    var scrollToAnker = function(event) {
        event.preventDefault();
        var ziel = $(this).attr("href");
        var target = ($.browser.opera) ? 'html' : 'html,body';
        $(target).animate({
            scrollTop: $(ziel).offset().top
        }, 500 , function (){location.hash = ziel + '/'});
    };

    var root = location.protocol + '//' + location.host;
    var childErsteEl = '.menuItem:first a';
    if(window.location.href == root + '/') {
        $(childErsteEl).css('opacity', '1');}
    else if(window.location.href == root + '/#*') {
        $(childErsteEl).css('opacity', '0');}

    var theLink = 'a[href*=#]';
    $(theLink).bind("click", scrollToAnker);
    return false;

};
    function init_scroll(event, delta) {
        var deltaOfInterest = delta,
            timeNow = new Date().getTime(),
            quietPeriod = 500;

        // Cancel scroll if currently animating or within quiet period
        if(timeNow - lastAnimation < quietPeriod + settings.animationTime) {
            event.preventDefault();
            return;
        }
        if (deltaOfInterest < 0) {
            el.moveDown()}
        else {
            el.moveUp()}
        lastAnimation = timeNow;}
    $(document).bind('mousewheel DOMMouseScroll', function(event) {
        event.preventDefault();
        var delta = event.originalEvent.wheelDelta || -event.originalEvent.detail;
        init_scroll(event, delta);
    });


}($);


/*!--------------------------
 * JavaScript Library v1.0.2
 * Copyright 2015 TrendTec UG
 */


var element = document.documentElement;

if(element.scrollHeight > element.clientHeight) {
    // Overflow detected; force scroll bar
    element.style.overflow = 'scrollbar';
} else {
    // No overflow detected; prevent scroll bar
    element.style.overflow = 'hidden';
}

$(document).ready(function() {
    var addClassToNav = '#nav li';
    $(addClassToNav).click(function() {
        $(addClassToNav).removeClass('active');
        $(this).addClass("active");return false;});
    $("html").onepage_scroll();
});






