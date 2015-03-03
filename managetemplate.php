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
// safe_require('artefact', 'internal');
raise_memory_limit("512M");

// Turn on autodetecting of line endings, so mac newlines (\r) will work
ini_set('auto_detect_line_endings', 1);

safe_require('artefact', 'rubric');
define('TITLE', get_string('managetemplate','artefact.rubric'));
$form = ArtefactTyperubricTemplate::get_form();

$offset = param_integer('offset', 0);
$limit  = param_integer('limit', 10);

$rubric = array(
		'count'  => 0,
		'data'   => array(),
		'offset' => $offset,
		'limit'  => $limit,
);

// $rubric = ArtefactTyperubric::get_rubric($offset, $limit);
$data = ArtefactTyperubric::get_template_list();
$rubric['count'] = count($data);
$rubric['data'] = $data;
ArtefactTyperubricTemplate::build_rubrictemplate_list_html($rubric);

$smarty =& smarty();

$smarty->assign_by_ref('rubric', $rubric);

$smarty->assign_by_ref('form', $form);
$smarty->assign_by_ref('PAGEHEADING', hsc(TITLE));
$smarty->display('artefact:rubric:managetemplate.tpl');

