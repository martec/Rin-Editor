﻿var MYBB_SMILIES={},smileyMapdes2=dropdownsmiliesdes.concat(dropdownsmiliesdesmore),isWebkit="WebkitAppearance"in document.documentElement.style,i;for(i in smileyMapurl)MYBB_SMILIES[smileyMapurl[i]]=smileyMapdes2[i];function isOrContains(a,e){for(;a;){if(a===e)return!0;a=a.parentNode}return!1}
function elementContainsSelection(a){var e;if(window.getSelection){if(e=window.getSelection(),0<e.rangeCount){for(var f=0;f<e.rangeCount;++f)if(!isOrContains(e.getRangeAt(f).commonAncestorContainer,a))return!1;return!0}}else if((e=document.selection)&&"Control"!=e.type)return isOrContains(e.createRange().parentElement(),a);return!1}
function quick_quote(a,e,f){function c(b){b=$(b.target);b.hasClass("post")||(b=b.parents(".post"));b&&b.length?isWebkit||$.trim(window.getSelection().toString())?elementContainsSelection(b.find(".post_body")[0])?(b=window.getSelection().getRangeAt(0).getBoundingClientRect(),$elm=$("#qr_pid_"+a+"").show(),$elm.css({top:window.scrollY+b.top-$elm.outerHeight()-4+"px",left:window.scrollX+b.left-($elm.outerWidth()-b.width)/2+"px"})):$("#qr_pid_"+a+"").hide():$("#qr_pid_"+a+"").hide():$("#qr_pid_"+a+"").hide()}
$(".new_reply_button").length&&$("#quick_reply_form").length&&($("#pid_"+a+"").mousemove(c).click(c),$('body:not("#pid_'+a+'")').click(function(b){$.trim(window.getSelection().toString())||$("#qr_pid_"+a+"").hide()}),$("#qr_pid_"+a+"").click(function(b){b.preventDefault();setTimeout(function(){if(elementContainsSelection(document.getElementById("pid_"+a+""))){Thread.quickQuote(a,""+e+"",f);$("#qr_pid_"+a+"").hide();var b=window.getSelection?window.getSelection():document.selection;b&&(b.removeAllRanges?
b.removeAllRanges():b.empty&&b.empty())}else $("#qr_pid_"+a+"").hide()},200)}))}Thread.quickQuote=function(a,e,f){if(isWebkit||window.getSelection().toString().trim())userSelection=window.getSelection().getRangeAt(0).cloneContents(),a=parseInt(rinvbquote)?"[quote\x3d"+e+";"+a+"]\n":"[quote\x3d'"+e+"' pid\x3d'"+a+"' dateline\x3d'"+f+"']\n",a+=Thread.domToBB(userSelection,MYBB_SMILIES),a+="\n[/quote]\n",delete userSelection,Thread.updateMessageBox(a)};
Thread.updateMessageBox=function(a){MyBBEditor.insertText(a,"","","","quote");setTimeout(function(){offset=$("#quickreply_e").offset().top-60;setTimeout(function(){$("html, body").animate({scrollTop:offset},700)},200)},100)};Thread.RGBtoHex=function(a,e,f){return Thread.toHex(a)+Thread.toHex(e)+Thread.toHex(f)};
Thread.toHex=function(a){if(null==a)return"00";a=parseInt(a);if(0==a||isNaN(a))return"00";a=Math.max(0,a);a=Math.min(a,255);a=Math.round(a);return"0123456789ABCDEF".charAt((a-a%16)/16)+"0123456789ABCDEF".charAt(a%16)};
Thread.domToBB=function(a,e){for(var f="",c,b,g,d,h=0;h<a.childNodes.length;h++)if(c=a.childNodes[h],d=g=b="","undefined"==typeof c.tagName)switch(c.nodeName){case "#text":f+=c.data.replace(/[\n\t]+/,"")}else{switch(c.tagName){case "SPAN":switch(!0){case "underline"==c.style.textDecoration:b="[u]";d="[/u]";break;case 0<c.style.fontWeight:case "bold"==c.style.fontWeight:b="[b]";d="[/b]";break;case "italic"==c.style.fontStyle:b="[i]";d="[/i]";break;case ""!=c.style.fontFamily:b="[font\x3d"+c.style.fontFamily+
"]";d="[/font]";break;case ""!=c.style.fontSize:b="[size\x3d"+c.style.fontSize+"]";d="[/size]";break;case ""!=c.style.color:-1!=c.style.color.indexOf("rgb")?(b=c.style.color.replace("rgb(","").replace(")","").split(","),b="#"+Thread.RGBtoHex(parseInt(b[0]),parseInt(b[1]),parseInt(b[2]))):b=c.style.color,b="[color\x3d"+b+"]",d="[/color]"}break;case "STRONG":case "B":b="[b]";d="[/b]";break;case "EM":case "I":b="[i]";d="[/i]";break;case "U":b="[u]";d="[/u]";break;case "IMG":e[c.src]?(b="",g=e[c.src],
d=""):(b="[img]",g=c.src,d="[/img]");break;case "A":switch(!0){case 0==c.href.indexOf("mailto:"):b="[email\x3d"+c.href.replace("mailto:","")+"]";d="[/email]";break;default:b="[url\x3d"+c.href+"]",d="[/url]"}break;case "OL":b="[list\x3d"+c.type+"]";d="\n[/list]";break;case "UL":b="[list]";d="\n[/list]";break;case "LI":b="\n[*]";d="";break;case "BLOCKQUOTE":c.removeChild(c.firstChild);b="[quote]\n";d="\n[/quote]";break;case "DIV":c.style.textAlign&&(b="[align\x3d"+c.style.textAlign+"]\n",d="\n[/align]\n");
switch(c.className){case "codeblock":b="[code]\n";d="\n[/code]";c.removeChild(c.getElementsByTagName("div")[0]);break;case "codeblock phpcodeblock":g=c.getElementsByTagName("code")[0],c.removeChild(c.getElementsByTagName("div")[0]),b="[php]\n",g=g.innerText?g.innerText:g.innerHTML.replace(/<br([^>]*)>/gi,"\n").replace(/<([^<]+)>/gi,"").replace(/&nbsp;/gi," "),d="\n[/php]"}break;case "P":d="\n\n";break;case "BR":d="\n"}f+=b+g;""==g&&c.childNodes&&0<c.childNodes.length&&(f+=Thread.domToBB(c,e));f+=
d}return f};