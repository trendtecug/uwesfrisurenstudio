

!function($) {

    var defaults = {
        sectionContainer: "section",

};

$.fn.onepage_scroll = function(options) {
    var settings = $.extend({}, defaults, options);
}

}($);


/*!--------------------------
 * JavaScript Library v1.0.2
 * t").top =t").top = -window.height();
 * -window.height();
 * opyright 2015 TrendTec UG
 */
var scrollToAnker2 = function() {
var ziel = location.hash.replace("/", "");
if (typeof TweenMax != "undefined")
{
TweenMax.killAll();
TweenMax.to( $(ziel).find(".cont-left"),0.1 , {scale:1});


window.actualScrolling = true;
 var ziel = location.hash.replace("/", "");
var right = $(ziel).find(".cont-right");
var left = $(ziel).find(".cont-left");
var h = $(window).height();
$(ziel).find(".cont-right").css('position', 'absolute');
$(ziel).find(".cont-left").css('position', 'absolute');
$(ziel).find(".cont-right").css('top', 0);

$(ziel).find(".container").css('zIndex',3000);

var start = 0;
var end = 0;

        $("section").each(function () {
        
        if ("#" + $(this).attr("id") == ziel) {
	   return false;
        }
        end++;
        });
        $("section").each(function () {
        if ($(this).find(".container").css("display") == "block") {
          return false; 
       }
	start++;
	});

if (start == 7)
start = 0;
$(ziel).find(".cont-left").css('top', -$( window ).height());
if (start > end)
 $(ziel).find(".cont-left").css('top', $( window ).height());
$(ziel).find(".cont-right").css('z-index',  200).css('position', ' relative');
$(ziel).find(".container").css('display', 'block');
$(ziel).find(".cont-right").css('opacity', 0);
var animationTime = 1.5;
target = ($.browser.opera) ? 'html' : 'html,body';
    TweenMax.to($(ziel).find(".cont-right"), animationTime, {
    opacity : 1.0,
    display: 'block', 
    ease: Expo.easeInOut	
    });

	TweenMax.to($(ziel).find(".cont-left"), animationTime,{ top: 0, ease: Expo.easeInOut,onComplete:  function (event) {
	$(ziel).find(".container").css('zIndex',100);
	$("section").each(function () {
        if ("#" + $(this).attr("id") !== ziel) {
	    $(this).find(".container").hide();
	    $(this).find(".container").css('zIndex',0);
	}
        TweenMax.to( $(this).find(".cont-left"), 20, {scale:1.1});
    });
    window.actualScrolling = false;

  var theLink = 'a[href*="' + location.hash.replace("/", "") + '"]';
  var theLi = $(theLink).parent();
var addClassToNav = '#nav li';
        $(addClassToNav).removeClass('active');
        $(theLi).addClass("active");

    }});

} else

setTimeout(scrollToAnker2, 50);
}
var scrollToAnker = function(event) {
    if (event)
	event.preventDefault();
    var ziel = $(this).attr("href");
    if (!ziel)
        ziel = location.hash.replace("/", "");
    var correct = false;
        $("section").each(function () {


        if ("#" + $(this).attr("id") == ziel) {
            correct = true;
        }
	});
    if (correct == false)
    {
	var childErsteEl = '.menuItem:first a';
	ziel = $(childErsteEl).attr("href");
    }
 if(window.actualScrolling == false) {
 
   location.hash = ziel + '/';
 }
};

$(document).ready(function() {

$("head").append('<script src="http://cdnjs.cloudflare.com/ajax/libs/gsap/1.17.0/TweenMax.min.js" type="text/javascript"></script>');
    window.actualScrolling = false;

    $(".logo").css("z-index", 10001);
    $(".logo").find("a").css("z-index", 10001);
    $("#nav").css("z-index", 10000);
    $(".container").css("width", "100%");
    var addClassToNav = '#nav li';
    var theLink = 'a[href*="#"]';
    var theLink2 = location.hash;
    $(".container").css("top",  0);
    $(".container").css("position", "absolute");
    $(".container").hide();
    $(addClassToNav).click(function() {
	 if(window.actualScrolling == false) {
        $(addClassToNav).removeClass('active');
        $(this).addClass("active");return false;
         }
});


    var root = location.protocol + '//' + location.host;
    var childErsteEl = '.menuItem:first a';
/*    if(window.location.href == root + '/') {
        $(childErsteEl).css('opacity', '1');}
    else if(theLink) {
        $(childErsteEl).css('opacity', '0');}
*/
$(window).on('hashchange', function() {
  if(window.actualScrolling == false)
	  scrollToAnker2();
  });
    if (theLink2 == "" || theLink2 == "/")
    {
	var href = $(childErsteEl).attr("href");
	location = root + href + "/";
    } else
    {
	scrollToAnker();
	scrollToAnker2();
    }

    
    $(theLink).bind("click", scrollToAnker);
    return false;
});




