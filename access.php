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
safe_require('artefact', 'rubric');

define('TITLE', get_string('editaccess', 'artefact.rubric'));

require_once('pieforms/pieform.php');
require_once('pieforms/pieform/elements/calendar.php');

$id = param_integer('id', 0);

$form = array(
    'name' => 'editaccess',
    'renderer' => 'div',
    'plugintype' => 'core',
    'pluginname' => 'artefact/rubric',
    'elements' => array(
        'id' => array(
            'type' => 'hidden',
            'value' => $id,
        ),
    )
);

global $SESSION;
$SESSION->set('rubric', $id);

$form['elements']['accesslist'] = array(
		'type'          => 'rubricacl',
);

if (!function_exists('strptime')) {
    // Windows doesn't have this, use an inferior version
    function strptime($date, $format) {
        $result = array(
            'tm_sec'  => 0, 'tm_min'  => 0, 'tm_hour' => 0, 'tm_mday'  => 1,
            'tm_mon'  => 0, 'tm_year' => 0, 'tm_wday' => 0, 'tm_yday'  => 0,
        );
        $formats = array(
            '%Y' => array('len' => 4, 'key' => 'tm_year'),
            '%m' => array('len' => 2, 'key' => 'tm_mon'),
            '%d' => array('len' => 2, 'key' => 'tm_mday'),
            '%H' => array('len' => 2, 'key' => 'tm_hour'),
            '%M' => array('len' => 2, 'key' => 'tm_min'),
        );
        while ($format) {
            $start = substr($format, 0, 2);
            switch ($start) {
            case '%Y': case '%m': case '%d': case '%H': case '%M':
                $result[$formats[$start]['key']] = substr($date, 0, $formats[$start]['len']);
                $format = substr($format, 2);
                $date = substr($date, $formats[$start]['len']);
            default:
                $format = substr($format, 1);
                $date = substr($date, 1);
            }
        }
        if ($result['tm_mon'] < 1 || $result['tm_mon'] > 12
            || $result['tm_mday'] < 1 || $result['tm_mday'] > 31
            || $result['tm_hour'] < 0 || $result['tm_hour'] > 23
            || $result['tm_min'] < 0 || $result['tm_min'] > 59) {
            return false;
        }
        return $result;
    }
}

function ptimetotime($ptime) {
    return mktime(
        $ptime['tm_hour'],
        $ptime['tm_min'],
        $ptime['tm_sec'],
        1,
        $ptime['tm_yday'] + 1,
        $ptime['tm_year'] + 1900
    );
}
function editaccess_cancel_submit() {
	redirect(get_config('wwwroot').'artefact/rubric/managetemplate.php');
}
function editaccess_submit(Pieform $form, $values) {
    global $SESSION;

    try {

    	db_begin();

    	delete_records('artefact_rubric_access', 'rubric', $values['id']);

    	if($values['accesslist']) {

    		$accesslist = $values['accesslist'];

    		foreach($accesslist as $access) {

    			$startdate = empty($access['startdate']) ? NULL : $access['startdate'];
    			$stopdate = empty($access['stopdate']) ? NULL : $access['stopdate'];

    			if($access['type'] == 'loggedin') {
    				$insertobj = (object)array('rubric' => $values['id'], 'loggedin' => 1, 'startdate' => $startdate, 'stopdate' => $stopdate);
    			}else if($access['type'] == 'usr') {
    				$insertobj = (object)array('rubric' => $values['id'], 'usr' => $access['id'], 'startdate' => $startdate, 'stopdate' => $stopdate);
    			}else if($access['type'] == 'user') {
    				$insertobj = (object)array('rubric' => $values['id'], 'usr' => $access['id'], 'startdate' => $startdate, 'stopdate' => $stopdate);
    			}else if($access['type'] == 'group') {
    				$insertobj = (object)array('rubric' => $values['id'], 'group' => $access['id'], 'startdate' => $startdate, 'stopdate' => $stopdate);
    			}else if($access['type'] == 'institution') {
    				$insertobj = (object)array('rubric' => $values['id'], 'institution' => $access['id'], 'startdate' => $startdate, 'stopdate' => $stopdate);
    			}

    			insert_record('artefact_rubric_access', $insertobj);
    		}
    	}
    	db_commit();
    }catch(Exception $e) {
    	db_rollback();
    }

    $SESSION->add_ok_msg(get_string('updateaccess', 'artefact.rubric'));
    redirect(get_config('wwwroot').'artefact/rubric/managetemplate.php');
}

$form['elements']['submit'] = array(
    'type'  => 'submitcancel',
    'value' => array(get_string('save'), get_string('cancel')),
);

$pieform = pieform($form);

$smarty = smarty(
    array('tablerenderer'),
    array(),
    array(),
    array('sidebars' => false)
);

$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('form', $pieform);
$smarty->display('artefact:rubric:access.tpl');