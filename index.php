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
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'rubric');
define('SECTION_PAGE', 'index');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'rubric');

define('TITLE', get_string('rubric','artefact.rubric'));

// offset and limit for pagination
$offset = param_integer('offset', 0);
$limit  = param_integer('limit', 10);

$rubric = array(
            'count'  => 0,
            'data'   => array(),
            'offset' => $offset,
            'limit'  => $limit,
        );

$rubric = ArtefactTyperubric::get_rubric($offset, $limit);
ArtefactTyperubric::build_rubric_list_html($rubric);

$js = <<< EOF
addLoadEvent(function () {
    {$rubric['pagination_js']}
});
EOF;

$smarty = smarty(array('paginator'));
$smarty->assign_by_ref('rubric', $rubric);
//2013/04/23 mod : テンプレート作成はサイト管理者のみ
$smarty->assign('isAdmimOrStuff', $USER->get('admin'));
// $smarty->assign('isAdmimOrStuff', ($USER->get('admin') || $USER->get('staff')));

$smarty->assign('strnorubricaddone',
    get_string('norubricaddone', 'artefact.rubric',
    '<a href="' . get_config('wwwroot') . 'artefact/rubric/new.php">', '</a>'));
$smarty->assign('PAGEHEADING', hsc(get_string("rubric", "artefact.rubric")));
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->display('artefact:rubric:index.tpl');
