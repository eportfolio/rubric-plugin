<?php
/*
  : A radar graph
*/

// Standard inclusions

define('INTERNAL', true);
define('MENUITEM', 'content/rubric');

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');

require_once(get_config('docroot') . 'artefact/rubric/pchart/class/pData.class.php');
require_once(get_config('docroot') . 'artefact/rubric/pchart/class/pDraw.class.php');
require_once(get_config('docroot') . 'artefact/rubric/pchart/class/pScatter.class.php');
require_once(get_config('docroot') . 'artefact/rubric/pchart/class/pImage.class.php');

define("DRAW_FONT",    get_config('docroot') . "artefact/rubric/pchart/fonts/sawarabi-gothic-medium.ttf");

$id = param_integer('id', 0);
$owner = param_integer('owner', $USER->get('id'));
if($id == 0) return false;

require_once(get_config('docroot') . 'artefact/rubric/lib.php');
$scores = ArtefactTyperubric::get_line_score($id, $owner);
$years = ArtefactTyperubric::get_rubric_year($id);
if(count($years) <= 1) return false; //時系列が1つだけの時は表示しない

$base = ArtefactTyperubric::get_rubric_data($id);


/* Create and populate the pData object */
$MyData = new pData();
$MyData->setAxisName(0,get_string('attainment', 'artefact.rubric'));

$names = array();
foreach ($scores as $key => $score) {
	foreach ($score as $value) {
		if($cnt == 1){
			$ytitle[] = $value->ytitle;
		}
		if($value->default_flg == 1){
			$points[] = $value->point;
		}else{
			$points[] = VOID;
		}
	}
	// スコアのデータセット
	$MyData->addPoints($points,$score[0]->stitle);
	$MyData->setSerieWeight($score[0]->stitle,1);
	$cnt += 1;

	$points = array();
}

$MyData->addPoints($ytitle,"Serie1");
$MyData->setSerieDescription("Serie1","test");
$MyData->setAbscissa("Serie1");



/* Create the pChart object */
$myPicture = new pImage(900,280,$MyData);

/* Turn of Antialiasing */
$myPicture->Antialias = FALSE;

/* Draw a background */
$Settings = array("R"=>255, "G"=>255, "B"=>255, "Dash"=>0, "DashR"=>210, "DashG"=>223, "DashB"=>127);
$myPicture->drawFilledRectangle(0,0,900,280,$Settings);

/* Add a border to the picture */
// $myPicture->drawRectangle(0,0,699,229,array("R"=>0,"G"=>0,"B"=>0));

/* Write the chart title */
$myPicture->setFontProperties(array("FontName"=>DRAW_FONT,"FontSize"=>11));
$myPicture->drawText(10,18,$base[0]->title,array("FontSize"=>10,"Align"=>TEXT_ALIGN_TOPLEFT));

/* Set the default font */
$myPicture->setFontProperties(array("FontName"=>DRAW_FONT,"FontSize"=>10));

/* Define the chart area */
$myPicture->setGraphArea(50,60,600,220);

/* Draw the scale */
$scaleSettings = array("XMargin"=>10,"YMargin"=>10,"Floating"=>TRUE,"GridR"=>200, "GridG"=>200,"GridB"=>200,"DrawSubTicks"=>TRUE, "CycleBackground"=>TRUE);
$myPicture->drawScale($scaleSettings);

/* Turn on Antialiasing */
$myPicture->Antialias = TRUE;

/* Draw the line chart */
$myPicture->drawLineChart();
$myPicture->drawPlotChart(array("PlotBorder"=>TRUE,"BorderSize"=>1,"Surrounding"=>-60,"BorderAlpha"=>60));

/* Write the chart legend */
$myPicture->drawLegend(610,70,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_VERTICAL));

/* Render the picture (choose the best way) */
// $myPicture->autoOutput("pictures/example.drawSplineChart.simple.png");
$myPicture->stroke();

// function get_score($id) {
// 	global $view;
// 	$result = get_records_sql_array("SELECT sk.id, y.id yid, y.title ytitle, st.point, sk.title stitle, s.default_flg FROM {artefact_rubric_score} s
// 			INNER JOIN {artefact_rubric_year} y ON s.year = y.id
// 			INNER JOIN {artefact_rubric_standard} st ON s.standard = st.id
// 			INNER JOIN {artefact_rubric_skill} sk ON s.skill = sk.id
// 			WHERE y.rubric = ? AND s.usr = ?
// 			ORDER BY sk.id, y.id
// 			", array($id, $view->get('owner'))) ;
// 	// 			var_dump($USER->get('id'));exit;

// 	if($result === false) return false;

// 	$ret = array();
// 	foreach ($result as $value) {
// 		$ret[$value->id][] = $value;
// 	}
// 	// var_dump($ret);exit;
// 	return $ret;
// }



?>