/*
 Copyright (c) 2003-2021, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
*/
(function(){var c=function(b){var a=b._.code.langs,d=b.lang.codesnippet,c=document.documentElement.clientHeight,f=[],e;f.push([b.lang.common.notSet,""]);for(e in a)f.push([a[e],e]);a=CKEDITOR.document.getWindow().getViewPaneSize();b=Math.min(a.width-70,800);a=a.height/1.5;650>c&&(a=c-220);return{title:d.title,minHeight:200,resizable:CKEDITOR.DIALOG_RESIZE_NONE,contents:[{id:"info",elements:[{id:"code",type:"textarea",label:d.codeContents,setup:function(a){this.setValue(a.data.code)},commit:function(a){a.setData("code",
this.getValue())},required:!0,validate:CKEDITOR.dialog.validate.notEmpty(d.emptySnippetError),inputStyle:"cursor:auto;width:"+b+"px !important;height:"+a+"px !important;tab-size:4;text-align:left;","class":"cke_source"}]}]}};CKEDITOR.dialog.add("code",c);CKEDITOR.dialog.add("php",c)})();