<?php
	/** Include PHPExcel */
require dirname(__FILE__).'/PHPExcel/Classes/PHPExcel.php';
require_once dirname(__FILE__).'/PHPExcel/Classes/PHPExcel/IOFactory.php';

$excel_path = dirname(__FILE__).'/downloads/';

function getExcelObject($filename) {
	global $excel_path;

	$isnew = false;

	if (file_exists($excel_path.$filename)) {
		$objPHPExcel = PHPExcel_IOFactory::load($excel_path.$filename);
	} else {
		$objPHPExcel = new PHPExcel();
		$isnew = true;
	}
	
	$objPHPExcel->setActiveSheetIndex(0);

	return array($objPHPExcel, $isnew);
}

function writeToExcel($filename, $objPHPExcel) {
	global $excel_path;

	$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
	$objWriter->save($excel_path.$filename);
}

function getParseQueryArray($parseStr) {
	$get_array = [];

	if($parseStr !== '')
		parse_str($parseStr, $get_array);

	return $get_array;
}

function storeDataToExcel($filename, $data) {
	$objInfo = getExcelObject($filename);
	$objPHPExcel = $objInfo[0];
	$isnew = $objInfo[1];

	$colK = 'A';
	$row = $objPHPExcel->getActiveSheet()->getHighestRow()+1;

	if ($isnew) {
		foreach ($data as $col => $value) {
		    $objPHPExcel->getActiveSheet()->SetCellValue($colK.$row, $col);
		    $colK++;
		}
	}

	$colK = 'A';
	$row = $objPHPExcel->getActiveSheet()->getHighestRow()+1;
	// $objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, $queryArray['CallSid']);

	foreach ($data as $col => $value) {
	    $objPHPExcel->getActiveSheet()->SetCellValue($colK.$row, $value);
	    $colK++;
	}

	writeToExcel($filename, $objPHPExcel);
}

function storeVoiceData($queryArray) {
	$filename = "voicemail.xlsx";
	storeDataToExcel($filename, $queryArray);
}

function storeCallAttemptData($queryArray) {
	$filename = "callattempt.xlsx";
	storeDataToExcel($filename, $queryArray);
}


$queryStr = trim($_SERVER['QUERY_STRING']);
error_log($queryStr);
$queryArray = getParseQueryArray($queryStr);

if ($queryArray) {

	if ($queryArray['CallType'] === 'voicemail') {
		storeVoiceData($queryArray);
	} else if ($queryArray['CallType'] === 'call-attempt') {
		storeCallAttemptData($queryArray);
	}

} else {
	error_log("No Parameters Provided");
}

?>