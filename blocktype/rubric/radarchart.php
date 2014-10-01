<?php

define('INTERNAL', true);
define('MENUITEM', 'content/rubric');
//2013/07/31 SCSK ADD START
define('INSTALLER', 1);
//2013/07/31 SCSK ADD END

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/init.php');

require_once(get_config('docroot') . 'artefact/rubric/pchart/class/pData.class.php');
require_once(get_config('docroot') . 'artefact/rubric/pchart/class/pDraw.class.php');
require_once(get_config('docroot') . 'artefact/rubric/pchart/class/pRadar.class.php');
require_once(get_config('docroot') . 'artefact/rubric/pchart/class/pImage.class.php');

define("DRAW_FONT",    get_config('docroot') . "artefact/rubric/pchart/fonts/sawarabi-gothic-medium.ttf");

$id = param_integer('id', 0);
$owner = param_integer('owner', $USER->get('id'));
if($id == 0) return false;

require_once(get_config('docroot') . 'artefact/rubric/lib.php');
$scores = ArtefactTyperubric::get_radar_score($id, $owner); //スコア
$base = ArtefactTyperubric::get_rubric_data($id); //ルーブリック
$maxpoint = get_standard_maxpoint($id); //評価基準最大ポイント
// var_dump($scores);exit;
if($scores == array()) return false;
// データセット用オブジェクトを生成
$MyData = new pData();

$cnt = 1;
$ccnt = 0;
$colorcnt = 1;
$isedit = true;
$rcolors = array(255,128,0,0,255,220,0,128,128,165);
$gcolors = array(165,0,0,128,255,20,191,128,128,42);
$bcolors = array(0,128,255,0,0,0,255,0,128,42);

$stitle = array();
$points = array();

foreach ($scores as $key => $score) { //時系列
	foreach ($score as $value) { //スキル
		if($cnt == 1){
			array_push($stitle, $value->stitle); //外側のタイトルを設定
			$ccnt += 1; //ポイントの数を取得
		}
		$points[] = $value->point; //ポイントを設定
		if($value->default_flg == 0) $isedit = false;
	}

	if($isedit){
		$MyData->addPoints($points,"SKILL_".$colorcnt);
		$ytitle = ($score[0]->ytitle == "") ? get_string('attainment','artefact.rubric') : $score[0]->ytitle;
		$MyData->setSerieDescription("SKILL_".$colorcnt,$ytitle);
		$MyData->setPalette(array("SKILL_".$colorcnt),array("Alpha"=>100, "R"=>$rcolors[$colorcnt-1],"G"=>$gcolors[$colorcnt-1],"B"=>$bcolors[$colorcnt-1]));
		$colorcnt++;
	}

	$isedit = true;
	$cnt += 1;

	$points = array();
}

// チャートの項目
$MyData->addPoints($stitle,"Skill");
$MyData->setAbscissa("Skill");

// グラフのサイズとデータセットを引数に渡してpchartオブジェクトを生成
$myPicture = new pImage(700,320,$MyData);

// 背景色と背景色に入れる斜線の色を指定
// $Settings = array("R"=>255, "G"=>255, "B"=>255, "Dash"=>0, "DashR"=>190, "DashG"=>203, "DashB"=>107);
$Settings = array("R"=>255, "G"=>255, "B"=>255, "Dash"=>0, "DashR"=>255, "DashG"=>153, "DashB"=>0);
// 背景を描く
$myPicture->drawFilledRectangle(0,0,700,370,$Settings);

// グラフのタイトルをセット
$myPicture->setFontProperties(array("FontName"=>DRAW_FONT,"FontSize"=>8));
// グラフタイトルを書く
$myPicture->drawText(10,18,$base[0]->title,array("R"=>0,"G"=>0,"B"=>0));

// フォンとサイズ、色のを切り替え
$myPicture->setFontProperties(array("FontName"=>DRAW_FONT,"FontSize"=>9,"R"=>80,"G"=>80,"B"=>80));

// グラフに影を付ける
// $myPicture->setShadow(TRUE,array("X"=>5,"Y"=>5,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

// レーダーチャート作成用オブジェクトを生成
$SplitChart = new pRadar();

// レーダーチャートを書くX軸、Y軸、幅、高さ
$myPicture->setGraphArea(55,25,320,320);
// レーダーチャートのオプション
$Options = array(
		"FixedMax"=>$maxpoint, // データセットの最大値
		"WriteValues"=>FALSE, // データセットの値を表示
		"DrawPoly"=>FALSE, // データセットをつないだ領域を塗りつぶす
		"AxisRotation"=>-90, // グラフの回転角度
		"Layout"=>RADAR_LAYOUT_STAR, // レーダーチャートのレイアウト(星型)
		"LabelPos"=>RADAR_LABELS_HORIZONTAL, // ラベルの表示形式(水平)
		"BackgroundGradient"=> // レーダーチャート内のグラデーション
		array(
				"StartR"=>255,"StartG"=>255,"StartB"=>255,
				"StartAlpha"=>100,
				"EndR"=>255,"EndG"=>255,"EndB"=>255,
				"EndAlpha"=>50)
);

$myPicture->drawLegend(380,130,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_VERTICAL));

// レーダーチャートを描く
$SplitChart->drawRadar($myPicture,$MyData,$Options);

// 描いたグラフの保存
// $myPicture->render("mypic.png");

$myPicture->stroke();

// function get_score($id) {
// 	global $view;

// 	$result = get_records_sql_array("SELECT y.id, y.title ytitle, st.point, sk.title stitle, s.default_flg FROM {lo_score} s
// 			INNER JOIN {lo_year} y ON s.year = y.id
// 			INNER JOIN {lo_standard} st ON s.standard = st.id
// 			INNER JOIN {lo_skill} sk ON s.skill = sk.id
// 			WHERE y.rubric = ? AND s.usr = ?
// 			ORDER BY y.id, sk.id
// 			", array($id, $view->get('owner'))) ;

// 	if($result === false) return array();

// 	$ret = array();
// 	foreach ($result as $value) {
// 		$ret[$value->id][] = $value;
// 	}

// 	return $ret;
// }

//評価基準
function get_standard_maxpoint($id) {
	$result = get_records_sql_array("SELECT MAX(point) point FROM {lo_standard} WHERE rubric = ? ORDER BY id", array($id)) ;
	return $result[0]->point;
}

?>