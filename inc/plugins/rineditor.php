<?php
/**
 * Rin Editor (Powerd by CKEditor)
 * https://github.com/martec
 *
 * Copyright (C) 2015-2017, Martec
 *
 * Rin Editor is licensed under the GPL Version 3, 29 June 2007 license:
 *	http://www.gnu.org/copyleft/gpl.html
 *
 * @fileoverview Rin Editor (Powerd by CKEditor)
 * @author Martec
 * @requires jQuery and Mybb
 * @credits CKEditor (http://ckeditor.com/).
 */

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

define('RE_PLUGIN_VER', '0.9.7');

function rineditor_info()
{
	global $db, $lang;

	$lang->load('config_rineditor');

	$YE_description = <<<EOF
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
{$lang->rineditor_plug_desc}
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/pt_BR/i/scr/pixel.gif" width="1" height="1">
</form>
EOF;

	return array(
		'name'			=> 'Rin Editor',
		'description'	=> $YE_description,
		'website'		=> 'https://github.com/martec/Rin-Editor',
		'author'		=> 'martec',
		'authorsite'	=> 'http://community.mybb.com/user-49058.html',
		'version'		=> RE_PLUGIN_VER,
		'codename'		=> 'rineditor',
		'compatibility' => '18*'
	);

}

function rineditor_install()
{
	global $db, $lang, $mybb;

	$lang->load('config_rineditor');

	$query	= $db->simple_select("settinggroups", "COUNT(*) as counts");
	$dorder = $db->fetch_field($query, 'counts') + 1;

	$groupid = $db->insert_query('settinggroups', array(
		'name'		=> 'rineditor',
		'title'		=> 'Rin Editor',
		'description'	=> $lang->rineditor_sett_desc,
		'disporder'	=> $dorder,
		'isdefault'	=> '0'
	));

	$new_setting[] = array(
		'name'		=> 'rineditor_enb_quick',
		'title'		=> $lang->rineditor_enbquick_title,
		'description'	=> $lang->rineditor_enbquick_desc,
		'optionscode'	=> 'yesno',
		'value'		=> '1',
		'disporder'	=> 1,
		'gid'		=> $groupid
	);
	
	$new_setting[] = array(
		'name'		=> 'rineditor_language',
		'title'		=> $lang->rineditor_language_title,
		'description'	=> $lang->rineditor_language_desc,
		'optionscode'	=> 'select
'.$lang->rineditor_language_val.'',
		'value'		=> '',
		'disporder'	=> 2,
		'gid'		=> $groupid
	);
	
	$new_setting[] = array(
		'name'		=> 'rineditor_mobm_source',
		'title'		=> $lang->rineditor_mobms_title,
		'description'	=> $lang->rineditor_mobms_desc,
		'optionscode'	=> 'yesno',
		'value'		=> '1',
		'disporder'	=> 3,
		'gid'		=> $groupid
	);

	$new_setting[] = array(
		'name'		=> 'rineditor_quickquote',
		'title'		=> $lang->rineditor_quickquote_title,
		'description'	=> $lang->rineditor_quickquote_desc,
		'optionscode'	=> 'yesno',
		'value'		=> '1',
		'disporder'	=> 4,
		'gid'		=> $groupid
	);

	$new_setting[] = array(
		'name'		=> 'rineditor_smile',
		'title'		=> $lang->rineditor_smile_title,
		'description'	=> $lang->rineditor_smile_desc,
		'optionscode'	=> 'yesno',
		'value'		=> '1',
		'disporder'	=> 5,
		'gid'		=> $groupid
	);
	
	$new_setting[] = array(
		'name'		=> 'rineditor_smiley_sc',
		'title'		=> $lang->rineditor_scsmiley_title,
		'description'	=> $lang->rineditor_scsmiley_desc,
		'optionscode'	=> 'yesno',
		'value'		=> '0',
		'disporder'	=> 6,
		'gid'		=> $groupid
	);

	$new_setting[] = array(
		'name'		=> 'rineditor_autosave',
		'title'		=> $lang->rineditor_autosave_title,
		'description'	=> $lang->rineditor_autosave_desc,
		'optionscode'	=> 'yesno',
		'value'		=> '0',
		'disporder'	=> 7,
		'gid'		=> $groupid
	);
	
	$new_setting[] = array(
		'name'		=> 'rineditor_autosave_message',
		'title'		=> $lang->rineditor_autosavemsg_title,
		'description'	=> $lang->rineditor_autosavemsg_desc,
		'optionscode'	=> 'yesno',
		'value'		=> '1',
		'disporder'	=> 8,
		'gid'		=> $groupid
	);
	
	$new_setting[] = array(
		'name'		=> 'rineditor_sel_text',
		'title'		=> $lang->rineditor_seltext_title,
		'description'	=> $lang->rineditor_seltext_desc,
		'optionscode'	=> 'yesno',
		'value'		=> '1',
		'disporder'	=> 9,
		'gid'		=> $groupid
	);
	
	$new_setting[] = array(
		'name'		=> 'rineditor_partial_mode',
		'title'		=> $lang->rineditor_partial_title,
		'description'	=> $lang->rineditor_partial_desc,
		'optionscode'	=> 'yesno',
		'value'		=> '0',
		'disporder'	=> 10,
		'gid'		=> $groupid
	);

	$new_setting[] = array(
		'name'		=> 'rineditor_height_full',
		'title'		=> $lang->rineditor_heightf_title,
		'description'	=> $lang->rineditor_heightf_desc,
		'optionscode'	=> 'numeric',
		'value'		=> '250',
		'disporder'	=> 11,
		'gid'		=> $groupid
	);

	$new_setting[] = array(
		'name'		=> 'rineditor_height_other',
		'title'		=> $lang->rineditor_heighto_title,
		'description'	=> $lang->rineditor_heighto_desc,
		'optionscode'	=> 'numeric',
		'value'		=> '200',
		'disporder'	=> 12,
		'gid'		=> $groupid
	);

	$new_setting[] = array(
		'name'		=> 'rineditor_rmv_buttonsf',
		'title'		=> $lang->rineditor_buttonsf_title,
		'description'	=> $lang->rineditor_buttonsf_desc,
		'optionscode'	=> 'textarea',
		'value'		=> 'Subscript,Superscript',
		'disporder'	=> 13,
		'gid'		=> $groupid
	);

	$new_setting[] = array(
		'name'		=> 'rineditor_rmv_buttonso',
		'title'		=> $lang->rineditor_buttonso_title,
		'description'	=> $lang->rineditor_buttonsf_desc,
		'optionscode'	=> 'textarea',
		'value'		=> 'Subscript,Superscript',
		'disporder'	=> 14,
		'gid'		=> $groupid
	);

	$new_setting[] = array(
		'name'		=> 'rineditor_rulesf',
		'title'		=> $lang->rineditor_rulesf_title,
		'description'	=> $lang->rineditor_rules_desc,
		'optionscode'	=> 'textarea',
		'value'		=> '',
		'disporder'	=> 15,
		'gid'		=> $groupid
	);

	$new_setting[] = array(
		'name'		=> 'rineditor_rulesf_des',
		'title'		=> $lang->rineditor_rulesdesf_title,
		'description'	=> $lang->rineditor_rules_desc,
		'optionscode'	=> 'textarea',
		'value'		=> '',
		'disporder'	=> 16,
		'gid'		=> $groupid
	);

	$new_setting[] = array(
		'name'		=> 'rineditor_ruleso',
		'title'		=> $lang->rineditor_ruleso_title,
		'description'	=> $lang->rineditor_rules_desc,
		'optionscode'	=> 'textarea',
		'value'		=> '',
		'disporder'	=> 17,
		'gid'		=> $groupid
	);

	$new_setting[] = array(
		'name'		=> 'rineditor_ruleso_des',
		'title'		=> $lang->rineditor_rulesdeso_title,
		'description'	=> $lang->rineditor_rules_desc,
		'optionscode'	=> 'textarea',
		'value'		=> '',
		'disporder'	=> 18,
		'gid'		=> $groupid
	);

	$new_setting[] = array(
		'name'		=> 'rineditor_imgurapi',
		'title'		=> $lang->rineditor_imgur_title,
		'description'	=> $lang->rineditor_imgur_desc,
		'optionscode'	=> 'text',
		'value'		=> '',
		'disporder'	=> 19,
		'gid'		=> $groupid
	);

	$db->insert_query_multiple("settings", $new_setting);
	rebuild_settings();
}

function rineditor_is_installed()
{
	global $db;

	$query = $db->simple_select("settinggroups", "COUNT(*) as counts", "name = 'rineditor'");
	$rows  = $db->fetch_field($query, 'counts');

	return ($rows > 0);
}

function rineditor_uninstall()
{
	global $db;

	$groupid = $db->fetch_field(
		$db->simple_select('settinggroups', 'gid', "name='rineditor'"),
		'gid'
	);

	$db->delete_query('settings', 'gid=' . $groupid);
	$db->delete_query("settinggroups", "name = 'rineditor'");
	rebuild_settings();
}

function rineditor_activate()
{
	global $db;
	include_once MYBB_ROOT.'inc/adminfunctions_templates.php';

	$new_template_global['rinbutquick'] = "<script type=\"text/javascript\">
{\$editor_language}
var dropdownsmiliesurl = [{\$dropdownsmiliesurl}],
dropdownsmiliesdes = [{\$dropdownsmiliesdes}],
dropdownsmiliesname = [{\$dropdownsmiliesname}],
dropdownsmiliesurlmore = [{\$dropdownsmiliesurlmore}],
dropdownsmiliesdesmore = [{\$dropdownsmiliesdesmore}],
dropdownsmiliesnamemore = [{\$dropdownsmiliesnamemore}],
smileydirectory = '{\$rinsmiledir}',
rinsmileysc = '{\$rinscsmiley}',
rinstartupmode = '{\$sourcemode}',
rinmobsms = '{\$mybb->settings['rineditor_mobm_source']}',
rinlanguage = '{\$rinlang}',
rinheight = '{\$rin_height}',
rinrmvbut = '{\$rin_rmvbut}',
extrabut = '{\$rin_extbut}',
extrabutdesc = '{\$rin_extbutd}',
rinautosave = '{\$rin_autosave}',
rinautosavemsg = '{\$mybb->settings['rineditor_autosave_message']}',
rinvbquote = {\$rin_vbquote},
rinskin = '{\$rin_style}',
rinimgur = '{\$rin_imgur}',
seltext = '{\$mybb->settings['rineditor_sel_text']}',
partialmode = '{\$mybb->settings['rineditor_partial_mode']}';
</script>
<script type=\"text/javascript\" src=\"{\$mybb->asset_url}/jscripts/rin/editor/rineditor.js?ver=".RE_PLUGIN_VER."\"></script>
<script type=\"text/javascript\" src=\"{\$mybb->asset_url}/jscripts/rin/editor/ckeditor.js?ver=".RE_PLUGIN_VER."\"></script>
<script type=\"text/javascript\" src=\"{\$mybb->asset_url}/jscripts/rin/editor/adapters/jquery.js?ver=".RE_PLUGIN_VER."\"></script>
{\$quickquote}
{\$quickquotesty}
<script type=\"text/javascript\">
$('#message, #signature').ckeditor();

(\$.fn.on || \$.fn.live).call(\$(document), 'click', '.quick_edit_button', function () {
	ed_id = \$(this).attr('id');
	var pid = ed_id.replace( /[^0-9]/g, '');
	if (typeof CKEDITOR.instances['quickedit_'+pid] !== \"undefined\") {
		CKEDITOR.instances['quickedit_'+pid].destroy;
	}
	setTimeout(function() {
		CKEDITOR.replace( 'quickedit_'+pid );
		if (CKEDITOR.instances['quickedit_'+pid]) {
			setTimeout(function() {
				CKEDITOR.instances['quickedit_'+pid].focus();
			},1000);
		}
		offset = \$('#quickedit_'+pid).offset().top - 60;
		setTimeout(function() {
			\$('html, body').animate({
				scrollTop: offset
			}, 700);
			setTimeout(function() {
			  \$('#pid_'+pid).find('button[type=\"submit\"]').attr( 'id', 'quicksub_'+pid );
			},200);
		},200);
	},400);
});

(\$.fn.on || \$.fn.live).call(\$(document), 'click', 'button[id*=\"quicksub_\"]', function () {
	ed_id = \$(this).attr('id');
	var pid = ed_id.replace( /[^0-9]/g, '');

	CKEDITOR.instances['quickedit_'+pid].destroy();
});

/**********************************
 * Thread compatibility functions *
 **********************************/
if(typeof Thread !== 'undefined')
{
	var quickReplyFunc = Thread.quickReply,
	quickReplyDoneFunc = Thread.quickReplyDone;
	Thread.quickReply = function(e) {
		if(typeof CKEDITOR !== 'undefined') {
			CKEDITOR.instances['message'].updateElement();
			\$('form[id*=\"quick_reply_form\"]').bind('reset', function() {
				CKEDITOR.instances['message'].setData('');
				\$('#message').val('');
			});
		}

		return quickReplyFunc.call(this, e);
	};

	Thread.quickReplyDone = function(e) {
		if(typeof CKEDITOR !== 'undefined' && typeof automentionck === \"function\") {
			setTimeout(function() {
				if (CKEDITOR.instances['message'].mode == 'wysiwyg') {
					\$('.atwho-container').remove();
					automentionck( \$(CKEDITOR.instances['message'].document.getBody().\$) );
				}
			},300);
		}

		return quickReplyDoneFunc.call(this, e);
	};

	Thread.multiQuotedLoaded = function(request) {
		if(typeof CKEDITOR !== 'undefined') {
			var json = \$.parseJSON(request.responseText);
			if(typeof json == 'object')
			{
				if(json.hasOwnProperty(\"errors\"))
				{
					\$.each(json.errors, function(i, message)
					{
						\$.jGrowl(lang.post_fetch_error + ' ' + message);
					});
					return false;
				}
			}
				MyBBEditor.insertText(json.message,'','','','quote');
		}

		Thread.clearMultiQuoted();
		\$('#quickreply_multiquote').hide();
		\$('#quoted_ids').val('all');
	};
};
</script>";

	foreach($new_template_global as $title => $template)
	{
		$new_template_global = array('title' => $db->escape_string($title), 'template' => $db->escape_string($template), 'sid' => '-1', 'version' => '1806', 'dateline' => TIME_NOW);
		$db->insert_query('templates', $new_template_global);
	}

	$new_template['postbit_quickquote'] = "<div class=\"rin-qc\" style=\"display: none;\" id=\"qr_pid_{\$post['pid']}\"><span>{\$lang->postbit_button_quote}</span></div>
<script type=\"text/javascript\">
	\$(document).ready(function() {
		quick_quote({\$post['pid']},'{\$post['username']}',{\$post['dateline']});
	});
</script>";

	foreach($new_template as $title => $template2)
	{
		$new_template = array('title' => $db->escape_string($title), 'template' => $db->escape_string($template2), 'sid' => '-2', 'version' => '1806', 'dateline' => TIME_NOW);
		$db->insert_query('templates', $new_template);
	}

	find_replace_templatesets(
		'showthread',
		'#' . preg_quote('{$footer}') . '#i',
		'{$footer}{$rinbutquick}'
	);

	find_replace_templatesets(
		'private_quickreply',
		'#' . preg_quote('</textarea>') . '#i',
		'</textarea>{$rinbutquick}'
	);

	find_replace_templatesets(
		'showthread_quickreply',
		'#' . preg_quote('<span class="smalltext">{$lang->message_note}<br />') . '#i',
		'<span class="smalltext">{$lang->message_note}<br />{$smilieinserter}'
	);

	find_replace_templatesets(
		'private_quickreply',
		'#' . preg_quote('<span class="smalltext">{$lang->message_note}<br />') . '#i',
		'<span class="smalltext">{$lang->message_note}<br />{$smilieinserter}'
	);

	find_replace_templatesets(
		'postbit_classic',
		'#' . preg_quote('{$post[\'iplogged\']}') . '#i',
		'{$post[\'quick_quote\']}{$post[\'iplogged\']}'
	);

	find_replace_templatesets(
		'postbit',
		'#' . preg_quote('{$post[\'iplogged\']}') . '#i',
		'{$post[\'quick_quote\']}{$post[\'iplogged\']}'
	);

	find_replace_templatesets(
		'post_attachments_attachment_postinsert',
		'#' . preg_quote('onclick=') . '#i',
		'onclick="MyBBEditor.insertText(\'[attachment={$attachment[\'aid\']}]\');" desonclick='
	);

	$codebuttons_local = array('calendar_addevent', 'calendar_editevent', 'editpost', 'modcp_announcements_edit', 'modcp_announcements_new', 'modcp_editprofile', 'newreply', 'newthread', 'private_send', 'usercp_editsig', 'warnings_warn_pm');
	foreach ($codebuttons_local as &$local) {
		find_replace_templatesets(
			''.$local.'',
			'#' . preg_quote('{$codebuttons}') . '#i',
			'{$rinbutquick}'
		);
	}
}

function rineditor_deactivate()
{
	global $db;
	include_once MYBB_ROOT."inc/adminfunctions_templates.php";

	$db->delete_query("templates", "title IN('rinbutquick','postbit_quickquote')");

	find_replace_templatesets(
		'showthread',
		'#' . preg_quote('{$footer}{$rinbutquick}') . '#i',
		'{$footer}'
	);

	find_replace_templatesets(
		'private_quickreply',
		'#' . preg_quote('</textarea>{$rinbutquick}') . '#i',
		'</textarea>'
	);

	find_replace_templatesets(
		'showthread_quickreply',
		'#' . preg_quote('<span class="smalltext">{$lang->message_note}<br />{$smilieinserter}') . '#i',
		'<span class="smalltext">{$lang->message_note}<br />'
	);

	find_replace_templatesets(
		'private_quickreply',
		'#' . preg_quote('<span class="smalltext">{$lang->message_note}<br />{$smilieinserter}') . '#i',
		'<span class="smalltext">{$lang->message_note}<br />'
	);

	find_replace_templatesets(
		'postbit_classic',
		'#' . preg_quote('{$post[\'quick_quote\']}{$post[\'iplogged\']}') . '#i',
		'{$post[\'iplogged\']}'
	);
	
	find_replace_templatesets(
		'postbit',
		'#' . preg_quote('{$post[\'quick_quote\']}{$post[\'iplogged\']}') . '#i',
		'{$post[\'iplogged\']}'
	);	

	find_replace_templatesets(
		'post_attachments_attachment_postinsert',
		'#' . preg_quote('onclick="MyBBEditor.insertText(\'[attachment={$attachment[\'aid\']}]\');" desonclick=') . '#i',
		'onclick='
	);

	$codebuttons_local = array('calendar_addevent', 'calendar_editevent', 'editpost', 'modcp_announcements_edit', 'modcp_announcements_new', 'modcp_editprofile', 'newreply', 'newthread', 'private_send', 'usercp_editsig', 'warnings_warn_pm');
	foreach ($codebuttons_local as &$local) {
		find_replace_templatesets(
			''.$local.'',
			'#' . preg_quote('{$rinbutquick}') . '#i',
			'{$codebuttons}'
		);
	}
}

$plugins->add_hook('global_start', 're_cache');
function re_cache()
{
	global $templatelist, $mybb, $settings;

	if (isset($templatelist)) {
		$templatelist .= ',';
	}

	if (THIS_SCRIPT == 'showthread.php' || THIS_SCRIPT == 'private.php') {
		if($mybb->settings['rineditor_smile'] != 0 && $mybb->settings['rineditor_quickquote'] != 1) {
			$templatelist .= 'rinbutquick,smilieinsert,smilieinsert_smilie,smilieinsert_getmore';
		}
		elseif($mybb->settings['rineditor_quickquote'] != 0 && $mybb->settings['rineditor_smile'] != 1) {
			$templatelist .= 'rinbutquick,postbit_quickquote';
		}
		elseif($mybb->settings['rineditor_quickquote'] != 0 && $mybb->settings['rineditor_smile'] != 0) {
			$templatelist .= 'rinbutquick,postbit_quickquote,smilieinsert,smilieinsert_smilie,smilieinsert_getmore';
		}
		else {
			$templatelist .= 'rinbutquick';
		}
	}
	$plugin_local = array('calendar.php', 'editpost.php', 'modcp.php', 'newreply.php', 'newthread.php', 'private.php', 'usercp.php', 'warnings.php');
	foreach ($plugin_local as &$local) {
		if (THIS_SCRIPT == ''.$local.'') {
			$templatelist .= 'rinbutquick';
		}
	}
}

function rineditor_inserter_quick($smilies = true)
{
	global $db, $mybb, $theme, $templates, $lang, $smiliecache, $cache, $templatelist, $cache;

	if (!$lang->rineditor) {
		$lang->load('rineditor');
	}

	$editor_lang_strings = array(
		"editor_videourl" => "Video URL:",
		"editor_videotype" => "Video Type:",
		"editor_insert" => "Insert",
		"editor_description" => "Description (optional):",
		"editor_enterimgurl" => "Enter the image URL:",
		"editor_enterurl" => "Enter URL:",
		"editor_dailymotion" => "Dailymotion",
		"editor_metacafe" => "MetaCafe",
		"editor_mixer" => "Mixer",
		"editor_vimeo" => "Vimeo",
		"editor_youtube" => "Youtube",
		"editor_facebook" => "Facebook",
		"editor_liveleak" => "LiveLeak",
		"editor_twitch" => "Twitch",		
		"editor_insertvideo" => "Insert a video",
		"editor_more" => "More",
		"rineditor_restore" => "Restore",
		"rineditor_uploading" => "Uploading",
		"rineditor_fail" => "Fail",
	);
	$editor_language = "RinEditor = {\n";

	$editor_languages_count = count($editor_lang_strings);
	$i = 0;
	foreach($editor_lang_strings as $lang_string => $key)
	{
		$i++;
		$js_lang_string = str_replace("\"", "\\\"", $key);
		$string = str_replace("\"", "\\\"", $lang->$lang_string);
		$editor_language .= "\t\"{$js_lang_string}\": \"{$string}\"";

		if($i < $editor_languages_count)
		{
			$editor_language .= ",";
		}

		$editor_language .= "\n";
	}

	$editor_language .= "};";

	// Smilies
	$emoticon = "";
	$emoticons_enabled = "false";
	if($smilies && $mybb->settings['smilieinserter'] != 0 && $mybb->settings['smilieinsertercols'] && $mybb->settings['smilieinsertertot'])
	{
		$emoticon = ",emoticon";
		$emoticons_enabled = "true";

		if(!$smiliecache)
		{
			if(!is_array($smilie_cache))
			{
				$smilie_cache = $cache->read("smilies");
			}
			foreach($smilie_cache as $smilie)
			{
				if($smilie['showclickable'] != 0)
				{
					$smilie['image'] = str_replace("{theme}", $theme['imgdir'], $smilie['image']);
					$smiliecache[$smilie['sid']] = $smilie;
				}
			}
		}

		unset($smilie);

		if(is_array($smiliecache))
		{
			reset($smiliecache);

			$rinsmiledir = $dropdownsmiliesurl = $dropdownsmiliesdes = $dropdownsmiliesnam = $dropdownsmiliesurlmore = $dropdownsmiliesdesmore = $dropdownsmiliesnamemore = "";
			$i = 0;

			foreach($smiliecache as $smilie)
			{
				$finds = explode("\n", $smilie['find']);

				// Only show the first text to replace in the box
				$smilie['find'] = $finds[0];

				$find = htmlspecialchars_uni($smilie['find']);
				$image = htmlspecialchars_uni($smilie['image']);
				$name = htmlspecialchars_uni($smilie['name']);

				if($i < $mybb->settings['smilieinsertertot'])
				{
					$dropdownsmiliesurl .= '"'.$mybb->asset_url.'/'.$image.'",';
					$dropdownsmiliesdes .= '"'.$find.'",';
					$dropdownsmiliesname .= '"'.$name.'",';
					if (empty($rinsmiledir)) {
						$rinsmiledir = substr($dropdownsmiliesurl, 1, strrpos($dropdownsmiliesurl, '/'));
					}
				}
				else
				{
					$dropdownsmiliesurlmore .= '"'.$mybb->asset_url.'/'.$image.'",';
					$dropdownsmiliesdesmore .= '"'.$find.'",';
					$dropdownsmiliesnamemore .= '"'.$name.'",';
				}

				++$i;
			}
		}
	}

	$quickquote = $quickquotesty = $sourcemode = $rin_height = $rin_rmvbut = $rin_extbut = $rin_extbutd = $rin_imgur = $rin_autosave = $rinlang = $rinscsmiley = $rin_vbquote = "";

	if(strpos($templatelist,'showthread_quickreply') || strpos($templatelist,'private_quickreply')) {
		$rin_height = $mybb->settings['rineditor_height_other'];
		$rin_rmvbut = $mybb->settings['rineditor_rmv_buttonso'];
		$rin_extbut = $mybb->settings['rineditor_ruleso'];
		$rin_extbutd = $mybb->settings['rineditor_ruleso_des'];
	}
	else {
		$rin_height = $mybb->settings['rineditor_height_full'];
		$rin_rmvbut = $mybb->settings['rineditor_rmv_buttonsf'];
		$rin_extbut = $mybb->settings['rineditor_rulesf'];
		$rin_extbutd = $mybb->settings['rineditor_rulesf_des'];
	}

	$rin_imgur = $mybb->settings['rineditor_imgurapi'];
	if ($mybb->settings['rineditor_autosave']) {
		$rin_autosave = 'autosave';
	}

	if($mybb->settings['rineditor_quickquote'] == 1 && strpos($templatelist,'showthread_quickreply')) {
		$quickquote = "<script type=\"text/javascript\" src=\"".$mybb->asset_url."/jscripts/rin/editor/thread.quickquote.js?ver=".RE_PLUGIN_VER."\"></script>";
		$quickquotesty = "<link rel=\"stylesheet\" href=\"".$mybb->asset_url."/jscripts/rin/editor/quickquote.css?ver=".RE_PLUGIN_VER."\" type=\"text/css\" />";
	}

	if($mybb->user['sourceeditor'] == 1)
	{
		$sourcemode = "source";
	}
	else {
		$sourcemode = "wysiwyg";
	}

	$plu_vb = $cache->read("plugins");
	if($plu_vb['active']['vbquote']) {
		$rin_vbquote = 1;
	}
	else {
		$rin_vbquote = 0;
	}

	if(substr($theme['editortheme'], 0, 4) === "rin-") {
		$rin_style = substr($theme['editortheme'], 0, -4);
	}
	else {
		$rin_style = 'rin-moonocolor';
	}

	$rinlang = $mybb->settings['rineditor_language'];
	$rinscsmiley = $mybb->settings['rineditor_smiley_sc'];

	eval("\$rininsertquick = \"".$templates->get("rinbutquick")."\";");

	return $rininsertquick;
}

global $settings;

$plugins->add_hook('pre_output_page', 'rineditor_replace', 100);

$enbq = '';

if($settings['rineditor_enb_quick']) {
	$enbq = 'showthread_start';
}

$plugin_local = array($enbq, 'calendar_start', 'editpost_start', 'modcp_start', 'newreply_start', 'newthread_start', 'private_start', 'usercp_start', 'warnings_start');
foreach ($plugin_local as &$local) {
	$plugins->add_hook(''.$local.'', 'rineditor');
}

function rineditor_replace($page) {

	$page = str_replace(build_mycode_inserter('signature'), '', $page);
	$page = str_replace(build_mycode_inserter('message'), '', $page);

	return $page;
}

function rineditor () {

	global $rinbutquick;

	$rinbutquick = rineditor_inserter_quick();
}

if($settings['rineditor_quickquote'] && $settings['rineditor_enb_quick']) {
	$plugins->add_hook('postbit', 're_quickquote_postbit');
}

function re_quickquote_postbit(&$post)
{
	global $templates, $lang;

	$post['quick_quote'] = '';
	eval("\$post['quick_quote'] = \"" . $templates->get("postbit_quickquote") . "\";");

}

if($settings['rineditor_smile']) {
	$plugins->add_hook("showthread_start", "rineditor_quick");
	$plugins->add_hook("private_start", "rineditor_quick");
}

function rineditor_quick () {

	global $smilieinserter;

	$smilieinserter = build_clickable_smilies();
}
?>