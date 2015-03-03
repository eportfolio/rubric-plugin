<?php
/**
 * @package    mahara
* @subpackage artefact-rubric
* @author     SCSK Corporation
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
*
*/

define('INTERNAL', 1);
define('MENUITEM', 'content/rubric');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'rubric');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

$id = param_integer('id');
$year = param_integer('year', 0);

safe_require('artefact', 'rubric');

$rubric = ArtefactTyperubric::get_template_list($id);

$years = ArtefactTyperubric::get_years($id);
$skills = ArtefactTyperubric::get_skills($id);

if($year == 0) {
	$year = key($years);
}

$records = ArtefactTyperubric::get_statistics_data($id, $year);

$results = array();
$usrs = array();

foreach($records as $record) {

	$skill = $record->skill;
	$usr = $record->usr;

	$results[$skill][$usr] = array(
			'point' => $record->point,
			'label' => $record->label,
			'bgcolor' => $record->bgcolor,
			'default_flg' => $record->default_flg
	);

	$usrs[$usr] = $record->name;
}

$js = <<<EOF
function onClickDisplay(id) {
	var element = document.getElementById('skill_' + id);

	if(element.style.display == "none") {
		element.style.display = "block";
	}else{
		element.style.display = "none";
	}
}
EOF;

$skillsaverage = ArtefactTyperubric::get_skills_average($results);
$usrstotalaverage = ArtefactTyperubric::get_usrs_total_average($records);
$totalaverage = ArtefactTyperubric::get_average_total_average($usrstotalaverage);

define('TITLE', $rubric->title);

$smarty =& smarty();

$smarty->assign('rubric', $id);
$smarty->assign('results', $results);
$smarty->assign('years', $years);
$smarty->assign('skills', $skills);
$smarty->assign('usrs', $usrs);
$smarty->assign('skillsaverage', $skillsaverage);
$smarty->assign('usrstotalaverage', $usrstotalaverage);
$smarty->assign('totalaverage', $totalaverage);

$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign_by_ref('PAGEHEADING', hsc(TITLE));
$smarty->display('artefact:rubric:statistics.tpl');