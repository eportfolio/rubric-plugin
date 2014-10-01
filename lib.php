<?php
/**
 * @package    mahara
 * @subpackage artefact-rubric
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();
require_once(get_config('docroot').'artefact/lib.php');

class PluginArtefactrubric extends PluginArtefact {

    public static function get_artefact_types() {
        return array(
            'rubric',
            'rubrictemplate',
        );
    }

    public static function get_block_types() {
        return array();
    }

    public static function get_plugin_name() {
        return 'rubric';
    }

    public static function menu_items() {
        return array(
            'content/rubric' => array(
                'path' => 'content/rubric',
                'url'  => 'artefact/rubric/',
                'title' => get_string('rubric', 'artefact.rubric'),
                'weight' => 60,
            ),
        );
    }
}

class ArtefactTyperubric extends ArtefactType {

    public function __construct($id = 0, $data = null) {
        parent::__construct($id, $data);
        if (empty($this->id)) {
            $this->container = 1;
        }
    }

    public static function get_links($id) {
        return array();
    }

    public function delete() {
        if (empty($this->id)) {
            return;
        }

        db_begin();
//         parent::delete();
        db_commit();
    }

    public static function get_icon($options=null) {
    }

    public static function is_singular() {
        return false;
    }


    /**
     * This function returns a list of the given user's rubric.
     *
     * @param limit how many rubric to display per page
     * @param offset current page to display
     * @return array (count: integer, data: array)
     */
    public static function get_rubric($offset=0, $limit=10) {
        global $USER;

        ($rubric = get_records_sql_array("SELECT * FROM {lo_rubric} r
                                        WHERE r.id IN (SELECT y.rubric FROM lo_year y INNER JOIN lo_score s ON y.id = s.year WHERE s.usr = ?)
                                        ORDER BY r.id", array($USER->get('id')), $offset, $limit))
                                        || ($rubric = array());
        ($count = get_records_sql_array("SELECT COUNT(r.id) cnt FROM {lo_rubric} r
        		WHERE r.id IN (SELECT y.rubric FROM lo_year y INNER JOIN lo_score s ON y.id = s.year WHERE s.usr = ?)"
        		, array($USER->get('id'))))
						        		|| ($rubric = array());
        $result = array(
            'count'  => $count[0]->cnt,
            'data'   => $rubric,
            'offset' => $offset,
            'limit'  => $limit,
        );
        return $result;
    }

    /**
     * Builds the rubric list table
     *
     * @param rubric (reference)
     */
    public static function build_rubric_list_html(&$rubric) {
        $smarty = smarty_core();
        $smarty->assign_by_ref('rubrics', $rubric);
        $rubric['tablerows'] = $smarty->fetch('artefact:rubric:rubriclist.tpl');
        $pagination = build_pagination(array(
            'id' => 'rubriclist_pagination',
            'class' => 'center',
            'url' => get_config('wwwroot') . 'artefact/rubric/index.php',
            'jsonscript' => 'artefact/rubric/rubric.json.php',
            'datatable' => 'rubriclist',
            'count' => $rubric['count'],
            'limit' => $rubric['limit'],
            'offset' => $rubric['offset'],
            'firsttext' => '',
            'previoustext' => '',
            'nexttext' => '',
            'lasttext' => '',
            'numbersincludefirstlast' => false,
            'resultcounttextsingular' => get_string('rubric', 'artefact.rubric'),
            'resultcounttextplural' => get_string('rubric', 'artefact.rubric'),
        ));
        $rubric['pagination'] = $pagination['html'];
        $rubric['pagination_js'] = $pagination['javascript'];
    }

    public static function validate(Pieform $form, $values) {
        global $USER;

        if (!empty($values['options'])) {
        	if(!ArtefactTyperubric::check_rubric_score($values['options'])){
        		$form->set_error('submit', get_string('cantnewrubric', 'artefact.rubric'));
        	}
        }
    }

    public static function submit(Pieform $form, $values) {
        global $USER, $SESSION;

        ArtefactTyperubric::create_score($values['options'], $USER->get('id'));

//         $years = ArtefactTyperubric::get_rubric_year($values['options']);
//         $cells = ArtefactTyperubric::get_rubric_cell($values['options']);

//         db_begin();

//         foreach ($years as $year) {
//         	foreach ($cells as $cell) {
//         		$fordb = new StdClass;
//         		$fordb->{'usr'} = $USER->get('id');
//         		$fordb->{'skill'} = $cell->skill;
//         		$fordb->{'standard'} = $cell->standard;
//         		$fordb->{'year'} = $year->id;
//         		$fordb->{'comment'} = '';
//         		insert_record('lo_score', $fordb);
//         	}
//         }

//         db_commit();

        $SESSION->add_ok_msg(get_string('rubricsavedsuccessfully', 'artefact.rubric'));
        redirect('/artefact/rubric/edit/index.php?id='.$values['options']);

    }

    public static function create_score($id, $user) {

    	$years = ArtefactTyperubric::get_rubric_year($id);
    	$cells = ArtefactTyperubric::get_rubric_cell($id);

    	db_begin();

    	foreach ($years as $year) {
    		foreach ($cells as $cell) {
    			$fordb = new StdClass;
    			$fordb->{'usr'} = $user;
    			$fordb->{'skill'} = $cell->skill;
    			$fordb->{'standard'} = $cell->standard;
    			$fordb->{'year'} = $year->id;
    			$fordb->{'comment'} = '';
    			insert_record('lo_score', $fordb);
    		}
    	}

    	db_commit();
    }

    /**
    * Gets the new/edit rubric pieform
    *
    */
    public static function get_form($rubric=null) {
        require_once(get_config('libroot') . 'pieforms/pieform.php');
        $elements = call_static_method(generate_artefact_class_name('rubric'), 'get_rubricform_elements', $rubric);
        $elements['submit'] = array(
            'type' => 'submitcancel',
            'value' => array(get_string('saverubric','artefact.rubric'), get_string('cancel')),
            'goto' => get_config('wwwroot') . 'artefact/rubric/',
        );
        $rubricform = array(
            'name' => empty($rubric) ? 'addrubric' : 'editrubric',
            'plugintype' => 'artefact',
            'pluginname' => 'rubric',
            'validatecallback' => array(generate_artefact_class_name('rubric'),'validate'),
            'successcallback' => array(generate_artefact_class_name('rubric'),'submit'),
            'elements' => $elements,
        );

        return pieform($rubricform);
    }

    /**
    * Gets the new/edit fields for the rubric pieform
    *
    */
    public static function get_rubricform_elements($rubric) {
    	$data = ArtefactTyperubric::get_rubric_template();
    	foreach ($data as $value) {
    		$options[$value->id] = $value->title;
    	}

    	$elements = array();
        $elements['options'] = array(
        		'type' => 'select',
        		'title' => get_string('title', 'artefact.rubric'),
        		'rules' => array(
        				'required' => true,
        		),
        		'options' => $options,
        		'defaultvalue' => 'all');

        if (!empty($rubric)) {
            foreach ($elements as $k => $element) {
                $elements[$k]['defaultvalue'] = $rubric->get($k);
            }
            $elements['rubric'] = array(
                'type' => 'hidden',
                'value' => $rubric->id,
            );
        }

        return $elements;
    }


    public function render_self($options) {
    }

    public static function check_rubric_score($rubric) {
    	global $USER;
    	$result = get_records_sql_array("SELECT sc.id  FROM {lo_score} sc
    			INNER JOIN {lo_year} y ON sc.year = y.id
    			INNER JOIN {lo_rubric} r ON y.rubric = r.id
    			WHERE sc.usr = ? AND r.id = ?
    			", array($USER->get('id'), $rubric)) ;

    	if($result === false){
    		return true; //なければ
    	}else{
    		return false; //あれば
    	}
    }

    public static function get_rubric_template() {
    	return $result = get_records_sql_array("SELECT id,title FROM {lo_rubric} ORDER BY id") ;
    }

    public static function get_rubric_cell($id) {
    	return $result = get_records_sql_array("SELECT c.skill,c.standard FROM {lo_cell} c
								    			WHERE c.skill IN (SELECT id FROM {lo_skill} WHERE rubric = ?)
								    			AND c.standard = (SELECT MIN(id) FROM {lo_standard} WHERE rubric = ?)
								    			ORDER BY c.skill,c.standard", array($id,$id)) ;
    }

    public static function get_rubric_data($id) {
    	return $result = get_records_sql_array("SELECT title,description FROM {lo_rubric} WHERE id = ?", array($id)) ;
    }

    public static function get_rubric_year($id) {
    	return $result = get_records_sql_array("SELECT * FROM {lo_year} WHERE rubric = ? ORDER BY id", array($id)) ;
    }

    public static function get_rubric_skill($id) {
    	return $result = get_records_sql_array("SELECT * FROM {lo_skill} WHERE rubric = ? ORDER BY id", array($id)) ;
    }

    //評価基準
    public static function get_standard_point($id) {
    	$result = get_records_sql_array("SELECT * FROM {lo_standard} WHERE rubric = ? ORDER BY id", array($id)) ;
    	$ret = array();
    	foreach ($result as $value) {
    		$ret[$value->point] = $value->id;
    	}
    	return $ret;
    }

    //評価基準
    public static function get_rubric_standard($id) {
    	$result = get_records_sql_array("SELECT * FROM {lo_standard} WHERE rubric = ? ORDER BY id", array($id)) ;
    	return $result;
    }

    public static function get_cell_label($skill, $standard) {
    	$result = get_records_sql_array("SELECT label FROM {lo_cell} WHERE skill = ? AND standard = ?", array($skill, $standard)) ;
    	return $result[0]->label;
    }

    public static function get_score($skill, $year, $owner) {
    	return $result = get_records_sql_array("SELECT sc.id, c.label, a.id fileno, a.title, st.bgcolor, sc.comment, st.point, sc.default_flg FROM {lo_score} sc
    			INNER JOIN {lo_standard} st ON sc.standard = st.id
    			INNER JOIN {lo_cell} c ON sc.standard = c.standard AND sc.skill = c.skill
    			LEFT JOIN {lo_evidence} e ON sc.id = e.score
    			LEFT JOIN {artefact} a ON e.artefact = a.id
    			WHERE sc.skill = ? AND sc.year = ? AND sc.usr = ?
    			", array($skill, $year, $owner)) ;
    }

    public static function get_radar_score($id, $owner) {
    	$result = get_records_sql_array("SELECT y.id, y.title ytitle, st.point, sk.title stitle, s.default_flg FROM {lo_score} s
    			INNER JOIN {lo_year} y ON s.year = y.id
    			INNER JOIN {lo_standard} st ON s.standard = st.id
    			INNER JOIN {lo_skill} sk ON s.skill = sk.id
    			WHERE y.rubric = ? AND s.usr = ?
    			ORDER BY y.id, sk.id
    			", array($id, $owner)) ;

    	if($result === false) return array();

    	$ret = array();
    	foreach ($result as $value) {
    		$ret[$value->id][] = $value;
    	}

    	return $ret;
    }

    public static function get_line_score($id, $owner) {
    	global $view;
    	$result = get_records_sql_array("SELECT sk.id, y.id yid, y.title ytitle, st.point, sk.title stitle, s.default_flg FROM {lo_score} s
    			INNER JOIN {lo_year} y ON s.year = y.id
    			INNER JOIN {lo_standard} st ON s.standard = st.id
    			INNER JOIN {lo_skill} sk ON s.skill = sk.id
    			WHERE y.rubric = ? AND s.usr = ?
    			ORDER BY sk.id, y.id
    			", array($id, $owner)) ;

    	if($result === false) return false;

    	$ret = array();
    	foreach ($result as $value) {
    		$ret[$value->id][] = $value;
    	}
    	return $ret;
    }
}

class ArtefactTyperubricTemplate extends ArtefactType {

	public function __construct($id = 0, $data = null) {
		parent::__construct($id, $data);
		if (empty($this->id)) {
			$this->container = 1;
		}
	}

	public static function get_links($id) {
		return array();
	}

	public function delete() {
		if (empty($this->id)) {
			return;
		}

		db_begin();
		parent::delete();
		db_commit();
	}

	public static function get_icon($options=null) {
	}

	public static function is_singular() {
		return false;
	}


	/**
	 * This function returns a list of the given user's rubric.
	 *
	 * @param limit how many rubric to display per page
	 * @param offset current page to display
	 * @return array (count: integer, data: array)
	 */
	public static function get_rubrictemplate($offset=0, $limit=10) {
		global $USER;

		($rubrictemplate = get_records_sql_array("SELECT * FROM {artefact}
				WHERE owner = ? AND artefacttype = 'rubrictemplate'
				ORDER BY title ASC", array($USER->get('id')), $offset, $limit))
				|| ($rubrictemplate = array());
		$result = array(
				'count'  => count_records('artefact', 'owner', $USER->get('id'), 'artefacttype', 'rubrictemplate'),
				'data'   => $rubrictemplate,
				'offset' => $offset,
				'limit'  => $limit,
		);

		return $result;
	}


	public static function validate(Pieform $form, $values) {
		global $USER, $SESSION;

		if ($values['file']['size'] == 0) {
			$form->set_error('file', $form->i18n('rule', 'required', 'required', array()));
			return;
		}

		//CSVデータの取得
		$tmp = fopen($values['file']['tmp_name'], "r");
		while ($csvtmp[] = fgetcsv($tmp, "1024")) {
		}
		// 配列 $csv の文字コードをSJIS-winからUTF-8に変換
// 		mb_convert_variables("UTF-8", "SJIS-win", $csvtmp);

		$csv = array();
		foreach ($csvtmp as $row) {
			if($row !== false && !is_null($row[0])) $csv[] = $row;
		}

		$haserror = false;
		//CSVデータチェック
		if(count($csv) < 4){
			//最低4行
			$SESSION->add_error_msg(get_string('rubrictemplateerrormaxrow', 'artefact.rubric'));
			$haserror = true;
		}

		$standard_cnt = 0;
		for ($i = 0; $i < count($csv)-1; $i++) {
			switch ($i) {
				case '0': //一行目
					if(count($csv[$i]) != 2){
						//2列
						$SESSION->add_error_msg(get_string('rubrictemplateerror1requredcol', 'artefact.rubric'));
						$haserror = true;
					}elseif($csv[$i][0] == '' || $csv[$i][1] == ''){
						$SESSION->add_error_msg(get_string('rubrictemplateerror1requred', 'artefact.rubric'));
						$haserror = true;
					}
					break;
				case '1': //時系列データ
// 					if(count($csv[$i]) >= 12){
// 						//10列
// 						$SESSION->add_error_msg(get_string('rubrictemplateerror2maxcol', 'artefact.rubric'));
// 						$haserror = true;
// 					}
					break;
				case '2': //評価基準
					if(!isset($csv[$i]) || count($csv[$i]) === 0 || count($csv[$i]) % 3 > 0){
						//3列で一組
						$SESSION->add_error_msg(get_string('rubrictemplateerror3requred', 'artefact.rubric'));
						$haserror = true;
					}
// 					elseif(!isset($csv[$i]) || count($csv[$i]) === 0 || count($csv[$i]) > 24){
// 						//評価基準は8個まで
// 						$SESSION->add_error_msg(get_string('rubrictemplateerror3maxcol', 'artefact.rubric'));
// 						$haserror = true;
// 					}
					else{
						$iserror = false;
						//数値チェック
						for ($j = 1; $j < count($csv[$i]); $j++) {
							if(!is_numeric($csv[$i][$j])){
								$iserror = true;
							}
							$j += 2;
						}
						if($iserror){
							$SESSION->add_error_msg(get_string('rubrictemplateerror3number', 'artefact.rubric'));
							$haserror = true;
						}

						$iserror = false;
						//色指定チェック
						for ($j = 2; $j < count($csv[$i]); $j++) {
							if (!ereg("#[a-fA-F0-9]{6}$",$csv[$i][$j])){
								$iserror = true;
							}
							$j += 2;
						}
						if($iserror){
							$SESSION->add_error_msg(get_string('rubrictemplateerror3style', 'artefact.rubric'));
							$haserror = true;
						}
					}
					$standard_cnt = count($csv[$i]);
					break;
				default:
					//スキル
					if(!$haserror && (count($csv[$i]) !== (2 + $standard_cnt / 3))){
						$SESSION->add_error_msg(get_string('rubrictemplateerror4requred', 'artefact.rubric'));
						$haserror = true;
					}
			}
		}

		if($haserror){
			redirect(get_config('wwwroot') . 'artefact/rubric/newtemplate.php');
		}

	}

	public static function submit(Pieform $form, $values) {
		global $USER, $SESSION;

		$tmp = fopen($values['file']['tmp_name'], "r");
		while ($csvtmp[] = fgetcsv($tmp, "1024")) {
		}
		// 配列 $csv の文字コードをSJIS-winからUTF-8に変換
// 		mb_convert_variables("UTF-8", "SJIS-win", $csvtmp);
		$csv = array();
		foreach ($csvtmp as $row) {
			if($row !== false && !is_null($row[0])) $csv[] = $row;
		}

		db_begin();

		try {
			$id = 10;
			$st_ids = array();
			$sk_ids = array();
			for ($i = 0; $i < count($csv); $i++) {
				switch ($i) {
					case '0':
						$fordb = new StdClass;
						$fordb->{'title'} = $csv[$i][0];
						$fordb->{'description'} = $csv[$i][1];
						$id = insert_record('lo_rubric', $fordb, 'id', true);
						break;
					case '1':
							foreach ($csv[$i] as $r) {
								$fordb = new StdClass;
								$fordb->{'rubric'} = $id;
								$fordb->{'title'} = $r;
								$success = insert_record('lo_year', $fordb);//時系列
							}
						break;
					case '2':
							for ($j = 0; $j < count($csv[$i]); $j++) {
								$fordb = new StdClass;
								$fordb->{'rubric'} = $id;
								$fordb->{'title'} = $csv[$i][$j];
								$fordb->{'point'} = $csv[$i][$j+1];
								$fordb->{'bgcolor'} = $csv[$i][$j+2];
								$st_ids[] = insert_record('lo_standard', $fordb, 'id', true);//評価基準
								$j += 2;
							}
						break;
					default:
							//スキル
							$fordb = new StdClass;
							$fordb->{'rubric'} = $id;
							$fordb->{'title'} = $csv[$i][0];
							$fordb->{'description'} = $csv[$i][1];
							$sk_id = insert_record('lo_skill', $fordb, 'id', true);//スキル

							//セル
							for ($j = 1; $j < count($csv[$i])-1; $j++) {
								$fordb = new StdClass;
								$fordb->{'skill'} = $sk_id;
								$fordb->{'standard'} = $st_ids[$j-1];
								$fordb->{'label'} = $csv[$i][$j+1];
								$success = insert_record('lo_cell', $fordb, 'id', true);//セル
							}
				}
			}
		}
		catch (Exception $e) {
			log_info("Rubric import failed: " . $e->getMessage());
			db_rollback();
			$SESSION->add_error_msg(get_string('rubrictemplatesavederror', 'artefact.rubric'));
			redirect('/artefact/rubric/newtemplate.php');

		}

		db_commit();

		$SESSION->add_ok_msg(get_string('rubrictemplatesavedsuccessfully', 'artefact.rubric'));

		redirect('/artefact/rubric/newtemplate.php');
	}

	/**
	 * This method extends ArtefactType::commit() by adding additional data
	 * into the artefact_rubric_attainment table.
	 *
	 */
	public function commit($values) {
		return true;
	}

	/**
	 * Gets the new/edit rubric pieform
	 *
	 */
	public static function get_form($rubric=null) {
		require_once(get_config('libroot') . 'pieforms/pieform.php');
		$elements = call_static_method(generate_artefact_class_name('rubrictemplate'), 'get_rubrictemplateform_elements', $rubric);
		$elements['submit'] = array(
				'type' => 'submitcancel',
				'value' => array(get_string('saverubrictemplate','artefact.rubric'), get_string('cancel')),
				'goto' => get_config('wwwroot') . 'artefact/rubric/',
		);

		$rubricform = array(
				'name' => 'rubrictemplate',
				'plugintype' => 'artefact',
				'pluginname' => 'rubrictemplate',
				'validatecallback' => array(generate_artefact_class_name('rubrictemplate'),'validate'),
				'successcallback' => array(generate_artefact_class_name('rubrictemplate'),'submit'),
				'elements' => $elements,
		);

		return pieform($rubricform);
	}

	/**
	 * Gets the new/edit fields for the rubric pieform
	 *
	 */
	public static function get_rubrictemplateform_elements($rubric) {
		$elements = array('file' => array(
            'type' => 'file',
            'title' => get_string('csvfile', 'admin'),
            'description' => get_string('csvfiledescription', 'artefact.rubric'),
            'rules' => array(
                'required' => true
            )
        ),);

		if (!empty($rubric)) {
			foreach ($elements as $k => $element) {
				$elements[$k]['defaultvalue'] = $rubric->get($k);
			}
			$elements['rubric'] = array(
					'type' => 'hidden',
					'value' => $rubric->id,
			);
		}

		return $elements;
	}

	public function render_self($options) {
	}
}


