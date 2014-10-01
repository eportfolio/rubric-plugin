<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-xgadvisers
 * @author     SCSK
 *
 */

defined('INTERNAL') || die();

class PluginBlocktyperubric extends SystemBlockType {

    public static function get_title() {
        return get_string('title', 'blocktype.rubric/rubric');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.rubric/rubric');
    }

    public static function get_categories() {
        return array('general');
    }

     /**
     * Optional method. If exists, allows this class to decide the title for
     * all blockinstances of this type
     */

    public static function render_instance(BlockInstance $instance, $editing=false) {
    	global $view;

    	$configdata = $instance->get('configdata');
    	$smarty = smarty_core();
    	$smarty->assign('display_type', $configdata['display_type']);//0：レーダーチャート 1：折れ線グラフ 2:テーブル

    	$id = $configdata['rubric']; //ルーブリックIDの取得
    	$years = PluginBlocktyperubric::get_years($id); //時系列の取得
    	if($configdata['display_type'] == 2){//テーブル
    		$viewlist = array();
    		require_once(get_config('docroot') . 'artefact/rubric/lib.php');
    		if(PluginBlocktyperubric::check_rubric_score($id)){ //ルーブリックが作成済みか判断
	    		$skills = ArtefactTyperubric::get_rubric_skill($id); //スキル
	    		$standards = ArtefactTyperubric::get_standard_point($id); //評価基準
	    		$imglist = array();
	    		$point = 0;
	    		$default_flg = false; //行が編集済みかを確認する
	    		foreach ($skills as $skill) {
	    			foreach ($years as $year) {
	    				$cell = ArtefactTyperubric::get_score($skill->id, $year->id, $view->get('owner'));
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
	    				if($point < $cell[0]->point) $point = $cell[0]->point;
	    			}
	    			if($default_flg === true){
// 	    			if($point < count($standards)) $point += 1;
// 	    			$viewlist[$skill->id]
// 	    			[$year->id]->nextlabel = ArtefactTyperubric::get_cell_label($skill->id, $standards[$point]);
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
	    		$colors = ArtefactTyperubric::get_rubric_standard($id);
	    		$smarty->assign('colors', $colors);
	    		$smarty->assign('years', $years);
	    		$smarty->assign('skills', $skills);
	    		$smarty->assign('imglist', $imglist);
	    		$smarty->assign('viewlist', $viewlist);
	    		//2013/07/29 SCSK MOD
	    		$smarty->assign('width', (240 + count($years) * 120));
	    		//2013/07/29 SCSK MOD
    		}
    	}else{//レーダーチャート 折れ線グラフ
    		$chart = '';
    		if(PluginBlocktyperubric::check_rubric_score($id)){ //ルーブリックが作成済みか判断
    			if($configdata['display_type'] == 0){
    				//2013/07/31 SCSK MOD START
    				//$chart = '<img src="'.get_config('wwwroot') . 'artefact/rubric/edit/radarchart.php?id='.$configdata['rubric'].'&owner='.$view->get('owner').'" alt="レーダーチャート" />';
    				$chart = '<img src="'.get_config('wwwroot') . 'artefact/rubric/blocktype/rubric/radarchart.php?id='.$configdata['rubric'].'&owner='.$view->get('owner').'" alt="レーダーチャート" />';
    				//2013/07/31 SCSK MOD END
    			}else{
    				if(count($years) > 1){ //時系列が複数ある場合
    					//2013/07/31 SCSK MOD START
     					//$chart = '<img src="'.get_config('wwwroot') . 'artefact/rubric/edit/linechart.php?id='.$configdata['rubric'].'&owner='.$view->get('owner').'" alt="折れ線グラフ" />';
    					$chart = '<img src="'.get_config('wwwroot') . 'artefact/rubric/blocktype/rubric/linechart.php?id='.$configdata['rubric'].'&owner='.$view->get('owner').'" alt="折れ線グラフ" />';
    					//2013/07/31 SCSK MOD END
    				}else{
    					$message = get_string('nolinechart', 'blocktype.rubric/rubric');
    				}
    			}
    		}else{
    			$message = get_string('norubric', 'blocktype.rubric/rubric');
    		}
    		$smarty->assign('owner', $view->get('owner'));
    		$smarty->assign('id', $configdata['rubric']);
    		$smarty->assign('chart', $chart);
    		$smarty->assign('message', $message);
    	}

        return $smarty->fetch('blocktype:rubric:rubric.tpl');
    }

    //ルーブリック基本データが作成されているかを取得
    public static function check_rubric_score($rubric) {
    	global $view;
    	$result = get_records_sql_array("SELECT sc.id  FROM {lo_score} sc
    			INNER JOIN {lo_year} y ON sc.year = y.id
    			INNER JOIN {lo_rubric} r ON y.rubric = r.id
    			WHERE sc.usr = ? AND r.id = ?
    			", array($view->get('owner'), $rubric)) ;

    	if($result === false){
    		return false; //なければ
    	}else{
    		return true; //あれば
    	}
    }

    public static function get_years($id) {
    	return $result = get_records_sql_array("SELECT * FROM {lo_year} WHERE rubric = ? ORDER BY id", array($id)) ;
    }

    public static function has_instance_config() {
      return true;
    }

    public static function instance_config_form($instance) {

    	$configdata = $instance->get('configdata');
    	$userid = $instance->get_view()->get('owner');

    	$form = array(
    			'rubric' => array(
    					'type' => 'select',
    					'title' => get_string('rubric','blocktype.rubric/rubric'),
    					'rules' => array(
    							'required' => true,
    							'integer'  => true,
    					),
    					'defaultvalue' => (isset($configdata['rubric']) ? $configdata['rubric'] : '')
    			),
    			'display_type' => array(
    					'type' => 'select',
    					'title' => get_string('display_type', 'blocktype.rubric/rubric'),
    					'defaultvalue' => (isset($configdata['display_type']) ? $configdata['display_type'] : '')
    			)
    	);

    	$options_rubric = array();
    	$rs = get_recordset_sql("SELECT id,title FROM lo_rubric");
    	while($record = $rs->FetchRow()) {
    		$options_rubric[$record['id']] = $record['title'];
    	}

    	$form['rubric']['options'] = $options_rubric;

    	//dummy
    	$options_display_type = array(get_string('rader_chart', 'blocktype.rubric/rubric'),
    			get_string('line_chart', 'blocktype.rubric/rubric'),
    			get_string('rubric_table', 'blocktype.rubric/rubric'));

    	$form['display_type']['options'] = $options_display_type;

    	return $form;

    }

public static function get_instance_title(BlockInstance $instance) {
    	return get_string('title', 'blocktype.rubric/rubric');
    }
}
