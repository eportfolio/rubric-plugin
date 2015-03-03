<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-rubric
 * @author     SCSK Corporation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

function xmldb_artefact_rubric_upgrade($oldversion=0) {

	if ($oldversion < 2014122601) {

		//artefact_rubric table
		$table = new XMLDBTable('artefact_rubric');

		$field = new XMLDBField('deleted');
		$field->setType(XMLDB_TYPE_INTEGER);
		$field->setLength(1);
		$field->setNotNull(false);

		add_field($table, $field);

		$field = new XMLDBField('deletedusr');
		$field->setType(XMLDB_TYPE_INTEGER);
		$field->setLength(10);
		$field->setNotNull(false);
		add_field($table, $field);

		$field = new XMLDBField('deletedtimestamp');
		$field->setType(XMLDB_TYPE_DATETIME);
		$field->setNotNull(false);
		add_field($table, $field);

		$key = new XMLDBKey('artefact_rubric_ibfk_1');
		$key->setFields('deletedusr');
		$key->setRefTable('usr');
		$key->setRefFields('id');
		add_key($table, $key);

		//artefact_rubric_score table
		$table = new XMLDBTable('artefact_rubric_score');

		$field = new XMLDBField('cusr');
		$field->setType(XMLDB_TYPE_INTEGER);
		$field->setLength(10);
		$field->setNotNull(false);
		add_field($table, $field);
		$field = new XMLDBField('ctime');
		$field->setType(XMLDB_TYPE_DATETIME);
		$field->setNotNull(false);
		add_field($table, $field);
		$field = new XMLDBField('musr');
		$field->setType(XMLDB_TYPE_INTEGER);
		$field->setLength(10);
		$field->setNotNull(false);
		add_field($table, $field);
		$field = new XMLDBField('mtime');
		$field->setType(XMLDB_TYPE_DATETIME);
		$field->setNotNull(false);
		add_field($table, $field);

		$key = new XMLDBKey('artefact_rubric_score_fk5');
		$key->setFields('cusr');
		$key->setRefTable('usr');
		$key->setRefFields('id');
		add_key($table, $key);
		$key = new XMLDBKey('artefact_rubric_score_fk6');
		$key->setFields('musr');
		$key->setRefTable('usr');
		$key->setRefFields('id');
		add_key($table, $key);

		//artefact_rubric_evidence table
		$table = new XMLDBTable('artefact_rubric_evidence');

		$field = new XMLDBField('cusr');
		$field->setType(XMLDB_TYPE_INTEGER);
		$field->setLength(10);
		$field->setNotNull(false);
		add_field($table, $field);
		$field = new XMLDBField('ctime');
		$field->setType(XMLDB_TYPE_DATETIME);
		$field->setNotNull(false);
		add_field($table, $field);
		$field = new XMLDBField('musr');
		$field->setType(XMLDB_TYPE_INTEGER);
		$field->setLength(10);
		$field->setNotNull(false);
		add_field($table, $field);
		$field = new XMLDBField('mtime');
		$field->setType(XMLDB_TYPE_DATETIME);
		$field->setNotNull(false);
		add_field($table, $field);

		$key = new XMLDBKey('artefact_rubric_evidence_fk2');
		$key->setFields('cusr');
		$key->setRefTable('usr');
		$key->setRefFields('id');
		add_key($table, $key);
		add_key($table, $key);
		$key = new XMLDBKey('artefact_rubric_evidence_fk3');
		$key->setFields('musr');
		$key->setRefTable('usr');
		$key->setRefFields('id');
		add_key($table, $key);
		add_key($table, $key);

		//artefact_rubric_access table

		$table = new XMLDBTable('artefact_rubric_access');
		$table->addFieldInfo('rubric', XMLDB_TYPE_INTEGER, 20, null, XMLDB_NOTNULL, false, null, null, null);
		$table->addFieldInfo('loggedin', XMLDB_TYPE_INTEGER, 1, null, false, false, null, null, null);
		$table->addFieldInfo('usr', XMLDB_TYPE_INTEGER, 10, null, false, false, null, null, null);
		$table->addFieldInfo('group', XMLDB_TYPE_INTEGER, 10, null, false, false, null, null, null);
		$table->addFieldInfo('institution', XMLDB_TYPE_CHAR, 255, null, false, false, null, null, null);
		$table->addFieldInfo('startdate', XMLDB_TYPE_DATETIME, null, null, false, false, null, null, null);
		$table->addFieldInfo('stopdate', XMLDB_TYPE_DATETIME, null, null, false, false, null, null, null);
		create_table($table);

		copy(get_config('docroot').'artefact/rubric/rubricacl.php', get_config('docroot').'lib/form/elements/rubricacl.php');
		copy(get_config('docroot').'artefact/rubric/theme/raw/rubricacl.tpl', get_config('docroot').'theme/raw/templates/form/rubricacl.tpl');
	}
    return true;
}
