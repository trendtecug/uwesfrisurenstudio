/*<![CDATA[*/
// tooltiphelper.js
//
// helper functions for tooltips of the flatmgr

var IE = document.all?true:false

var posX = 0;
var posY = 0;

document.onmousedown = Mausklick;

function mDown(e){
	hideTooltip(); // if tooltip is visible
}

function ttwin (title, e) {
	var felder = title.split("#");

/*
if (/MSIE (\d+\.\d+);/.test(navigator.userAgent)){ //test for MSIE x.x;
  var ieversion=new Number(RegExp.$1) // capture x.x portion and store as a number
  if (ieversion>=9)
   document.write("You're using IE9 or above")
  else if (ieversion>=8)
   document.write("You're using IE8 or above")
  else if (ieversion>=7)
   document.write("You're using IE7.x")
  else if (ieversion>=6)
   document.write("You're using IE6.x")
  else if (ieversion>=5)
   document.write("You're using IE5.x")
 }
*/

	posX       = e.pageX ? pageXOffset + e.clientX + 20 - 180: document.documentElement.scrollLeft + e.x + 20 - 180 ;
	posY       = e.pageY ? pageYOffset + e.clientY - 130: document.documentElement.scrollTop  + e.y - 130;


	$html ='<table>';
	for (i=0; i< felder.length-1; i = i + 2) {
		if ( i == 0)
			$html +='<tr><td class="header">' + felder[i] + '</td><td class="headerValue">' + felder[i+1] + '</td></tr>';
		else
       		$html +='<tr><td class="field">' + felder[i] + '</td><td class="fieldValue">' + felder[i+1] + '</td></tr>';
	}
	$html += '</table>';
	//$html += posY + '' ;
  	if (document.getElementById("flatmgrTooltip") != null)
  		document.getElementById("flatmgrTooltip").innerHTML = $html;
    document.getElementById("flatmgrTooltip").style.display = "block";
    document.getElementById("flatmgrTooltip").style.top = posY+'px';
    document.getElementById("flatmgrTooltip").style.left = posX+'px';


}
function hideTooltip() {
	if (document.getElementById("flatmgrTooltip")) {
		if (document.getElementById("flatmgrTooltip").style.display == "block")
			document.getElementById("flatmgrTooltip").style.display = "none";
	}
}



function Mausklick(ereignis) {
	hideTooltip(); // if tooltip is visible
}




function findPosY(obj){
	var curtop = 0;
	if (document.getElementById || document.all) {
		while (obj.offsetParent) {
		curtop += obj.offsetTop;
		if (typeof(obj.scrollTop) == 'number')
		curtop -= obj.scrollTop;
		obj = obj.offsetParent;
	}
	}
	else if (document.layers)
		curtop += obj.y;
	if(navigator.appVersion.indexOf("MSIE 5") > -1) {
		curtop = curtop / 2;
	}
	return curtop;
}

function findPosX(obj) {
	var curleft = 0;
	if (document.getElementById || document.all) {
		while (obj.offsetParent) {
			curleft += obj.offsetLeft
			obj = obj.offsetParent;
		}
	}
	else if (document.layers)
	curleft += obj.x;
	return curleft;
}

/*]]>*/
