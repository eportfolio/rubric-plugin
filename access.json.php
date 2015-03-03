<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

// NOTE: this JSON script is used by the 'viewacl' element. It could probably
// be moved elsewhere without harm if necessary (e.g. if the 'viewacl' element
// was used in more places
define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('searchlib.php');

$type   = param_variable('type');
$query  = param_variable('query', '');
$limit  = param_integer('limit', 10);
$offset = param_integer('offset', 0);

switch ($type) {
    case 'institution':
//         $data = search_user($query, $limit, $offset,  array('exclude' => $USER->get('id'), 'friends' => true));
		$result = get_records_sql_array('select name, displayname from institution where displayname like ? and name != \'mahara\'', array('%'.$query.'%'));

		$data['count'] = count($result);
		$data['limit'] = 10;
		$data['offset'] = 0;
		$data['data'] = array();

		$cnt = 0;

		foreach($result as $r) {

			$data['data'][$cnt]['name'] = $r->displayname;
			$data['data'][$cnt]['url'] = get_config('wwwroot').'institution/index.php?institution='.$r->name;
			$cnt++;
		}

        break;
    case 'user':
        $data = search_user($query, $limit, $offset, array('exclude' => $USER->get('id')));
        break;
    case 'group':
        require_once('group.php');
        $data = search_group($query, $limit, $offset, '');
        $roles = get_records_array('grouptype_roles');
        $data['roles'] = array();
        foreach ($roles as $r) {
            $data['roles'][$r->grouptype][] = array('name' => $r->role, 'display' => get_string($r->role, 'grouptype.'.$r->grouptype));
        }
        foreach ($data['data'] as &$r) {
            $r->url = group_homepage_url($r);
        }
        break;
}

// $fp = fopen('/usr/local/mahara/kio/admin.log', 'a');
// ob_start();
// var_dump($data);
// $disp = ob_get_contents();
// ob_end_clean();

// fwrite($fp, $disp);
// fclose($fp);

$data['error'] = false;
$data['message'] = '';

json_reply(false, $data);
