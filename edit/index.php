<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-rubric
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', true);
define('MENUITEM', 'content/rubric');

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
require_once('pieforms/pieform.php');
require_once(get_config('docroot') . 'artefact/lib.php');
safe_require('artefact','rubric');

define('TITLE', get_string('editrubric','artefact.rubric'));

$id = param_integer('id');
// --> 2014.12.22 SCSK ADD
$show = param_integer('show', 0);
// <-- 2014.12.22 SCSK ADD

// --> 2014.12.22 SCSK ADD
$user = param_integer('user', 0) ? param_integer('user', 0) : $USER->get('id');
// <-- 2014.12.22 SCSK ADD


// $artefact = new ArtefactTyperubric($id);
// if (!$USER->can_edit_artefact($artefact)) {
//     throw new AccessDeniedException(get_string('accessdenied', 'error'));
// }

// $editform = ArtefactTyperubric::get_form($artefact);

$rubric = ArtefactTyperubric::get_rubric_data($id);
$years = ArtefactTyperubric::get_rubric_year($id);
$skills = ArtefactTyperubric::get_rubric_skill($id);
$standards = ArtefactTyperubric::get_standard_point($id);
$viewlist = array();
$imglist = array();
$point = 0;
$default_flg = false; //行が編集済みかを確認する

foreach ($skills as $skill) {
	foreach ($years as $year) {
		$cell = ArtefactTyperubric::get_score($skill->id, $year->id, $user);
		$viewlist[$skill->id][$year->id] = $cell[0];
		foreach ($cell as $value) {
			$imglist[$skill->id][$year->id][] = $value; //エビデンス用配列
		}
		if( $cell[0]->default_flg == 0){
			$cell[0]->bgcolor = "#FFFFFF"; //未設定の場合は背景色を白にする
			$cell[0]->point = 0; //2013/07/19 SCSK ADD
		}else{
			$default_flg = true;
		}
		$viewlist[$skill->id][$year->id] = $cell[0];
		if($point < $cell[0]->point) $point = $cell[0]->point; //最大ポイントの取得(次のステップ用)
	}
	if($default_flg === true){
		//次のステップを設定
		//2013/07/19 SCSK MOD START
		if($point < count($standards)){
			$point += 1;
			$viewlist[$skill->id][$year->id]->nextlabel = ArtefactTyperubric::get_cell_label($skill->id, $standards[$point]);
		}else{
			$viewlist[$skill->id][$year->id]->nextlabel = "-";
		}
		//2013/07/19 SCSK MOD END
		$default_flg = false;
	}else{
		$viewlist[$skill->id][$year->id]->nextlabel = "-";
	}
	$point = 0;
}

// var_dump($viewlist);exit;
$smarty = smarty();
$smarty->assign('rubric', $id);

$colors = ArtefactTyperubric::get_rubric_standard($id);
$smarty->assign('colors', $colors);

$radarchart = '<img src="'.get_config('wwwroot') . 'artefact/rubric/edit/radarchart.php?id='.$id.'"  />';
$linechart = '<img src="'.get_config('wwwroot') . 'artefact/rubric/edit/linechart.php?id='.$id.'"  />';
$smarty->assign('radarchart', $radarchart);
$smarty->assign('linechart', $linechart);
$smarty->assign('isyeardisplay', count($years) > 1); //時系列が2以上ならば折れ線グラフを表示する

//2013/07/29 SCSK MOD
// $smarty->assign('width', (240 + count($years) * 120));
// SCSK MOD END

// --> 2014.12.22 SCSK ADD
$smarty->assign('show', $show);
// <-- 2014.12.22 SCSK ADD
$smarty->assign('years', $years);
$smarty->assign('skills', $skills);
$smarty->assign('standards', $standards);
$smarty->assign('viewlist', $viewlist);
$smarty->assign('imglist', $imglist);
$smarty->assign('PAGEHEADING', $rubric[0]->title);
$smarty->display('artefact:rubric:rubric.tpl');


