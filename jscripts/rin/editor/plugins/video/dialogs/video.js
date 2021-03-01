/*
 Copyright (c) 2003-2016, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.md or http://ckeditor.com/license
*/
CKEDITOR.dialog.add("video",function(a){return{title:a.lang.video.title,minWidth:270,minHeight:120,contents:[{id:"video",label:a.lang.video.title,elements:[{type:"select",id:"videotype","default":"youtube",label:RinEditor["Video Type:"],items:[[RinEditor.Dailymotion,"dailymotion"],[RinEditor.Facebook,"facebook"],[RinEditor.LiveLeak,"liveleak"],[RinEditor.MetaCafe,"metacafe"],[RinEditor.Mixer,"mixer"],[RinEditor.Vimeo,"vimeo"],[RinEditor.Youtube,"youtube"],[RinEditor.Twitch,"twitch"]]},{type:"text",
id:"videolink",label:RinEditor["Video URL:"],"default":""}]}],onOk:function(){var b=this.getValueOf("video","videolink"),c=this.getValueOf("video","videotype");b&&MyBBEditor.insertText("[video\x3d"+c+"]"+b+"[/video]","",""+a.name+"_2")}}});