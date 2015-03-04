<?php
/**

 * @package    mahara
 * @subpackage artefact-rubric
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */


define('INTERNAL', true);
define('MENUITEM', 'content/rubric');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'rubric');
define('SECTION_PAGE', 'index');

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
require_once(get_config('libroot') . 'pieforms/pieform.php');

safe_require('artefact', 'file');
safe_require('artefact','rubric');

$id = param_integer('id');
$rubric = param_integer('rubric');

$rdata = get_rubric_base($rubric, $id);
define('TITLE', $rdata[0]->title);

if ($id) {
// 	$achievement = new ArtefactTypeAchievement($id);
// 	if (!$USER->can_edit_artefact($achievement)) {
// 	    throw new AccessDeniedException(get_string('accessdenied', 'error'));
// 	}
// 	$form = ArtefactTypeAchievement::get_form($rubric, $id);
}
else {
	throw new AccessDeniedException(get_string('accessdenied', 'error'));
}

$folder = param_integer('folder', 0);
$browse = (int) param_variable('browse', 0);
$highlight = null;
if ($file = param_integer('file', 0)) {
	$highlight = array($file);
}

$score = get_score($id);
$results = get_options($rubric, $id);
$options = $results['options'];
$labels = $results['labels'];

$sql = "SELECT artefact FROM {artefact_rubric_evidence} WHERE score = ".$id;
$rs = get_recordset_sql($sql);
$attachments =array();
$cnt = 0;
while($record = $rs->FetchRow()) {
	$attachments[$cnt] = $record['artefact'];
	$cnt++;
}

$defaultvalue = $score->standard;
//初期編集の時は「選択してください」を追加
if($score->default_flg == 0){
	$options[-1] = get_string('selectmessage', 'artefact.rubric');
	$defaultvalue = -1;
}
ksort($options);

$mscore = ArtefactTyperubric::get_score_musr_mtime($id);
$evidence = ArtefactTyperubric::get_evidence_musr_mtime($id);

$elements = array(
		'scoremodified' => array(
				'type' => 'html',
				'class' => 'description',
				'value' => $mscore ? get_string('lastmodified', 'artefact.rubric').$mscore->name.'('.$mscore->date.')' : '',
		),
		'options' => array(
				'type'         => 'select',
				'title'        => get_string('achievement', 'artefact.rubric'),
				'defaultvalue' => $defaultvalue,
				'options'      => $options,
				'rules' => array(
						'required' => true
				)
		),
		'label' => array(
				'type' => 'html',
				'title' => get_string('rubric','artefact.rubric'),
				'value' =>
				'<P id="rubric_rabel" style="font-weight : bold;color : green;"></P>'
		),
		'evidencemodified' => array(
				'type' => 'html',
				'class' => 'description',
				'value' => $evidence ? get_string('lastmodified', 'artefact.rubric').$evidence->name.'('.$evidence->date.')' : '',
		),
		'filebrowser' => array(
				'type'         => 'filebrowser',
				'title'        => get_string('attachments', 'artefact.rubric'),
				'folder'       => $folder,
				'highlight'    => $highlight,
				'browse'       => $browse,
				'page'         => get_config('wwwroot') . 'artefact/rubric/edit/achievement.php?id='.$id.'&rubric='.$rubric,
				'browsehelp'   => 'browsemyfiles',
				'config'       => array(
						'upload'          => true,
						'uploadagreement' => get_config_plugin('artefact', 'file', 'uploadagreement'),
						'resizeonuploaduseroption' => get_config_plugin('artefact', 'file', 'resizeonuploaduseroption'),
						'resizeonuploaduserdefault' => $USER->get_account_preference('resizeonuploaduserdefault'),
						'createfolder'    => false,
						'edit'            => false,
						'select'          => true,
				),
				'defaultvalue'       => $attachments,
				'selectlistcallback' => 'artefact_get_records_by_id',
				'selectcallback'     => 'add_attachment',
				'unselectcallback'   => 'delete_attachment',
		),
		'description' => array(
				'type'  => 'textarea',
				'rows' => 10,
				'cols' => 50,
				'resizable' => false,
				'defaultvalue' => $score->comment,
				'title' => get_string('comment', 'artefact.rubric'),
		),
		'standardid' => array(
				'type'  => 'hidden',
				'value' => 0,
		),
		'score' => array(
				'type'  => 'hidden',
				'value' => $id,
		),
		'rubric' => array(
				'type'  => 'hidden',
				'value' => $rubric,
		),
		'submitpost' => array(
				'type' => 'submitcancel',
				'value' => array(get_string('saveachievement','artefact.rubric'), get_string('cancel')),
				'goto' => get_config('wwwroot') . 'artefact/rubric/edit/index.php?id='.$rubric,
		)
);

foreach ($labels as  $key => $value) {
	$elements['hidden'.$key] = array(
				'type'  => 'hidden',
				'value' => $value);
}

$form = pieform(array(
		'name'               => 'editachievement',
		'method'             => 'post',
		'autofocus'          => $focuselement,
		'jsform'             => true,
		'newiframeonsubmit'  => true,
		'jssuccesscallback'  => 'editachievement_callback',
		'jserrorcallback'    => 'editachievement_callback',
		'plugintype'         => 'artefact',
		'pluginname'         => 'blog',
		'configdirs'         => array(get_config('libroot') . 'form/', get_config('docroot') . 'artefact/file/form/'),
		'elements'           => $elements
));

/*
 * Javascript specific to this page.  Creates the list of files
 * attached to the blog post.
 */
$wwwroot = get_config('wwwroot');
$noimagesmessage = json_encode(get_string('noimageshavebeenattachedtothispost', 'artefact.rubric'));
$javascript = <<<EOF



// Override the image button on the tinyMCE editor.  Rather than the
// normal image popup, open up a modified popup which allows the user
// to select an image from the list of image files attached to the
// post.

// Get all the files in the attached files list that have been
// recognised as images.  This function is called by the the popup
// window, but needs access to the attachment list on this page
function attachedImageList() {
    var images = [];
    var attachments = editachievement_filebrowser.selecteddata;
    for (var a in attachments) {
        if (attachments[a].artefacttype == 'image' || attachments[a].artefacttype == 'profileicon') {
            images.push({
                'id': attachments[a].id,
                'name': attachments[a].title,
                'description': attachments[a].description ? attachments[a].description : ''
            });
        }
    }
    return images;
}


function imageSrcFromId(imageid) {
    return config.wwwroot + 'artefact/file/download.php?file=' + imageid;
}

function imageIdFromSrc(src) {
    var artefactstring = 'download.php?file=';
    var ind = src.indexOf(artefactstring);
    if (ind != -1) {
        return src.substring(ind+artefactstring.length, src.length);
    }
    return '';
}

var imageList = {};

function blogpostImageWindow(ui, v) {
    var t = tinyMCE.activeEditor;

    imageList = attachedImageList();

    var template = new Array();

    template['file'] = '{$wwwroot}artefact/blog/image_popup.php';
    template['width'] = 355;
    template['height'] = 275 + (tinyMCE.isMSIE ? 25 : 0);

    // Language specific width and height addons
    template['width'] += t.getLang('lang_insert_image_delta_width', 0);
    template['height'] += t.getLang('lang_insert_image_delta_height', 0);
    template['inline'] = true;

    t.windowManager.open(template);
}

function editachievement_callback(form, data) {
    editachievement_filebrowser.callback(form, data);
}

addLoadEvent(function () {
    $('editachievement_options').onchange = changetitle;
    changetitle();
});
function changetitle() {
    obj = document.getElementById('editachievement_options');
    val = obj.options[obj.selectedIndex].value;
    rabel = document.getElementById('rubric_rabel');
    if(val > 0 ){
	    hidden = document.getElementById('editachievement_hidden'+val);
	    rabel.innerHTML = hidden.value;
    }else{
        rabel.innerHTML = '';
    }
}


EOF;

$smarty = smarty(array(), array(), array(), array(
		'tinymcesetup' => "ed.addCommand('mceImage', blogpostImageWindow);",
		'sideblocks' => array(
				array(
						'name'   => 'quota',
						'weight' => -10,
						'data'   => array(),
				),
		),
));
$smarty->assign('INLINEJAVASCRIPT', $javascript);

// $smarty = smarty();
$smarty->assign('editform', $form);
$smarty->assign('year', $rdata[0]->ytitle);
// --> 2014.12.18 SCSK ADD
$smarty->assign('description', $rdata[0]->sdescription);
// <-- 2014.12.18 SCSK ADD
$smarty->assign('PAGEHEADING', $rdata[0]->title.'/'.$rdata[0]->stitle);
$smarty->display('artefact:rubric:edit.tpl');

function editachievement_validate(Pieform $form, $values) {
	global $SESSION, $id, $rubric;

	if($values['options'] == -1) {
		$result = array(
				'error'   => true,
				'message' => get_string('achievementnosaved', 'artefact.rubric'),
				'goto'    => get_config('wwwroot') . 'artefact/rubric/edit/achievement.php?id='.$id.'&rubric='.$rubric,
		);

		// Redirect back to the blog page from within the iframe
		$SESSION->add_error_msg($result['message']);
		$form->json_reply(PIEFORM_ERR, $result, false);
		$form->reply(PIEFORM_ERR, $result);
	}

}

/**
 * This function get called to cancel the form submission. It returns to the
 * blog list.
 */
function editachievement_cancel_submit() {
	global $blog;
	redirect(get_config('wwwroot') . 'artefact/blog/view/?id=' . $blog);
}

function editachievement_submit(Pieform $form, $values) {
	global $USER, $SESSION, $blogpost, $blog;

	db_begin();
	$data = (object)array(
			'id'  => $values['score'],
			'standard'  => $values['options'],
			'comment' => $values['description'],
			'default_flg' => 1, //更新済みに設定
	);

	$default = get_record('artefact_rubric_score', 'id', $values['score']);

	$date = date('Y-m-d H:i:s');

	if($default->default_flg == 0) {
		$data->{'cusr'} = $USER->get('id');
		$data->{'ctime'} = $date;
	}

	$data->{'musr'} = $USER->get('id');
	$data->{'mtime'} = $date;

	$success = update_record('artefact_rubric_score', $data, 'id');

	$sql = "SELECT artefact FROM {artefact_rubric_evidence} WHERE score = ".$values['score'];
	$rs = get_recordset_sql($sql);
	$attachments =array();
	$cnt = 0;
	while($record = $rs->FetchRow()) {
		$attachments[$cnt] = $record['artefact'];
		$cnt++;
	}

	$new = is_array($values['filebrowser']) ? $values['filebrowser'] : array();

	foreach($attachments as $attachment) {
		if (!in_array($o, $new)) {
			$sql = "DELETE FROM {artefact_rubric_evidence} WHERE score = ".$values['score']." AND artefact = ".$attachment;
			execute_sql($sql);
		}
	}

	foreach($new as $file) {
		try{
			$sql = "INSERT INTO {artefact_rubric_evidence} VALUES(".$values['score'].",".$file.",{$USER->get('id')},'{$date}',{$USER->get('id')},'{$date}')";
			execute_sql($sql);
		}catch(Exception $e) {
		}
	}

	db_commit();

	$result = array(
			'error'   => false,
			'message' => get_string('achievementsaved', 'artefact.rubric'),
			'goto'    => get_config('wwwroot') . 'artefact/rubric/edit/index.php?id=' . $values['rubric'],
	);
	if ($form->submitted_by_js()) {
		// Redirect back to the blog page from within the iframe
		$SESSION->add_ok_msg($result['message']);
		$form->json_reply(PIEFORM_OK, $result, false);
	}
	$form->reply(PIEFORM_OK, $result);
}

function get_options($rubric, $id) {
	$result = get_records_sql_array("SELECT s.id, s.title, c.label FROM {artefact_rubric_standard} s
			INNER JOIN {artefact_rubric_cell} c ON s.id = c.standard
			WHERE s.rubric = ?
			AND c.skill = (SELECT MAX(skill) FROM {artefact_rubric_score} WHERE id = ?)
			ORDER BY s.id
			", array($rubric, $id)) ;
	$ret = array();
	$retlabel = array();
	// 		foreach ($result as $value) {
	// 			$ret[$value->id.':'.$value->label] = $value->title;
	// 		}
	foreach ($result as $value) {
		$ret[$value->id] = $value->title;
		$retlabel[$value->id] = $value->label;
	}

	return array('options'=>$ret, 'labels'=>$retlabel);
}

function get_score($id) {
	$result = get_records_sql_array("SELECT s.*, a.title filename, st.title FROM {artefact_rubric_score} s
			INNER JOIN {artefact_rubric_standard} st ON s.standard = st.id
			LEFT JOIN {artefact_rubric_evidence} e ON s.id = e.score
			LEFT JOIN {artefact} a ON e.artefact = a.id
			WHERE s.id = ?
			", array($id)) ;
	return $result[0];
}

function get_rubric_base($rubric, $id) {
	return $result = get_records_sql_array("SELECT r.title,s.title stitle, s.description sdescription, y.title ytitle FROM {artefact_rubric} r
			INNER JOIN {artefact_rubric_skill} s ON r.id = s.rubric
			INNER JOIN {artefact_rubric_year} y ON r.id = y.rubric
			WHERE r.id = ?
			AND s.id = (SELECT MAX(skill) FROM {artefact_rubric_score} WHERE id = ?)
			AND y.id = (SELECT MAX(year) FROM `artefact_rubric_score` WHERE id = ?)"
			, array($rubric, $id, $id)) ;
}

function add_attachment($attachmentid) {
	global $blogpostobj;
	if ($blogpostobj) {
		$blogpostobj->attach($attachmentid);
	}
}

function delete_attachment($attachmentid) {
	global $blogpostobj;
	if ($blogpostobj) {
		$blogpostobj->detach($attachmentid);
	}
}
