﻿var smileyMap={};smileyMapnam=dropdownsmiliesname.concat(dropdownsmiliesnamemore);smileyMapdes=dropdownsmiliesdes.concat(dropdownsmiliesdesmore);for(var i in smileyMapnam)smileyMap[smileyMapnam[i]]=smileyMapdes[i];
MyBBEditor={insertText:function(c,e,a,b,f){var d="",g=c,h=e;e||(e=h="");a||(a="message_2");d=a.slice(0,-2);b||(b="source");f||(f="");"source"==b?rinsourceditor.insert_text(c,e,a):(g=CKEDITOR.instances[d].dataProcessor.toDataFormat(CKEDITOR.instances[d].dataProcessor.toHtml(g+h),{context:"body"}),rinsourceditor.insert_text(g,"",a));"undefined"!==typeof CKEDITOR&&CKEDITOR.instances&&(d||(d="message"),"wysiwyg"==CKEDITOR.instances[d].mode&&("source"==b?(data=CKEDITOR.plugins.bbcode.BBCodeToHtml(g+h,
d),"quote"==f&&(data=data.replace(/([\s\S]*)<\/blockquote>/,"$1\x3c/blockquote\x3e\x3cbr\x3e")),CKEDITOR.instances[d].insertHtml(data)):CKEDITOR.instances[d].insertHtml(c+e)));return!1}};
rinsourceditor=function(){return{init:function(){return!0},insert_text:function(c,e,a){var b;a=document.getElementById(a);if(!a)return!1;if(document.selection&&document.selection.createRange)a.focus(),b=document.selection.createRange(),b.text=c+b.text+e;else if(a.selectionStart||0===a.selectionStart){b=a.selectionStart;var f=a.selectionEnd,d=a.scrollTop;a.value=a.value.substring(0,b)+c+a.value.substring(b,f)+e+a.value.substring(f,a.value.length);"\x3d"===c.charAt(c.length-2)?a.selectionStart=b+c.length-
1:a.selectionStart=b===f?f+c.length:f+c.length+e.length;a.selectionEnd=a.selectionStart;a.scrollTop=d}else a.value+=c+e;a.focus()}}}();