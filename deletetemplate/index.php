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

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
require_once('pieforms/pieform.php');
safe_require('artefact','rubric');

define('TITLE', get_string('deletetemplate','artefact.rubric'));

$id = param_integer('id');
$data = ArtefactTyperubric::get_rubric_data($id);

// $todelete = new ArtefactTypePlan($id);
// if (!$USER->can_edit_artefact($todelete)) {
//     throw new AccessDeniedException(get_string('accessdenied', 'error'));
// }

$deleteform = array(
    'name' => 'deleterubricform',
    'plugintype' => 'artefact',
    'pluginname' => 'rubric',
    'renderer' => 'div',
    'elements' => array(
        'submit' => array(
            'type' => 'submitcancel',
            'value' => array(get_string('deletetemplate','artefact.rubric'), get_string('cancel')),
            'goto' => get_config('wwwroot') . '/artefact/rubric/managetemplate.php',
        ),
    )
);
$form = pieform($deleteform);

$smarty = smarty();
$smarty->assign('form', $form);
$smarty->assign('PAGEHEADING', $data[0]->title);
$smarty->assign('subheading', get_string('deletethistemplate','artefact.rubric',$data[0]->title));
$smarty->assign('message', get_string('deletetemplateconfirm','artefact.rubric'));
$smarty->display('artefact:rubric:delete.tpl');

// calls this function first so that we can get the artefact and call delete on it
function deleterubricform_submit(Pieform $form, $values) {
    global $SESSION, $id, $USER;

    $vals = array($USER->get('id'), $id);
    db_begin();

    execute_sql('UPDATE artefact_rubric SET deleted = 1 WHERE id = ?', array($id));

    db_commit();

    $SESSION->add_ok_msg(get_string('rubricdeletedsuccessfully', 'artefact.rubric'));

    redirect('/artefact/rubric/managetemplate.php');
}
