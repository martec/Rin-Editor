/**
 * @license Copyright (c) 2003-2021, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here.
	// For complete reference see:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config

	if ((CKEDITOR.env.mobile || CKEDITOR.env.iOS) && parseInt(rinmobsms)) {
		rinstartupmode = 'source';
	}

	// The toolbar groups arrangement, optimized for a single toolbar row.
	config.toolbarGroups = [
		{ name: 'basicstyles' },
		{ name: 'align' },
		{ name: 'styles' },
		{ name: 'colors', groups: [ 'colors', 'cleanup' ] },
		{ name: 'insert' },
		{ name: 'links' },
		{ name: 'list' },
		{ name: 'blocks', groups: [ 'blocks', 'clipboard' ] },
		{ name: 'extra', groups: [ 'extra', 'extradesc' ] },
		{ name: 'undo' },
		{ name: 'document',	groups: [ 'tools', 'mode' ] }
	];

	config.language = rinlanguage;
	config.removePlugins = rinautosave;

	// The default plugins included in the basic setup define some buttons that
	// are not needed in a basic editor. They are removed here.
	config.removeButtons = 'Cut,Copy,Paste,Anchor,BGColor,indent,'+rinrmvbut+'';

	// Dialog windows are also simplified.
	config.removeDialogTabs = 'link:advanced';

	config.contentsCss = content_url;
	config.title = false;
	config.image_prefillDimensions = parseInt(rin_img_resize) ? true : false;
	config.height = rinheight;
	config.fontSize_sizes = 'xx-small;x-small;small;medium;large;x-large;xx-large';
	config.smiley_images = dropdownsmiliesurl.concat(dropdownsmiliesurlmore);
	config.smiley_descriptions = dropdownsmiliesname.concat(dropdownsmiliesnamemore);
	config.smiley_name = dropdownsmiliesdes.concat(dropdownsmiliesdesmore);
	config.smiley_path = smileydirectory;
	config.smiley_sc = rinsmileysc;
	config.startupMode = rinstartupmode;
	config.imgurClientId = rinimgur;
	config.disableNativeSpellChecker = false;
	config.skin = rinskin;
	config.image_previewText = ' ';

	config.autosave = {
		saveDetectionSelectors : 'input[name*="submit"],input[name*="savedraft"],input[id*="quick_reply_submit"],input[name*="previewpost"]',
		messageType : rinautosavemsg
	};
};