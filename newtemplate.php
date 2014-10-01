<?php
/**
 * @package    mahara
 * @subpackage artefact-rubric
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
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
define('TITLE', get_string('newrubrictemplate','artefact.rubric'));
$form = ArtefactTyperubricTemplate::get_form();

$smarty =& smarty();
$smarty->assign_by_ref('form', $form);
$smarty->assign_by_ref('PAGEHEADING', hsc(TITLE));
$smarty->display('artefact:rubric:newtemplate.tpl');

