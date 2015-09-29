<?php
	/** Include PHPExcel */
require dirname(__FILE__).'/PHPExcel/Classes/PHPExcel.php';
require_once dirname(__FILE__).'/PHPExcel/Classes/PHPExcel/IOFactory.php';

function getExcelObject($filename) {
	$objPHPExcel = PHPExcel_IOFactory::load($filename);
	$objPHPExcel->setActiveSheetIndex(0);

	return $objPHPExcel;
}

function writeToExcel($filename, $objPHPExcel) {
	$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
	$objWriter->save($filename);
}

function getParseQueryArray($parseStr) {
	$get_array = [];

	if($parseStr !== '')
		parse_str($parseStr, $get_array);

	return $get_array;
}

function storeDataToExcel($filename, $data) {
	$objPHPExcel = getExcelObject($filename);

	$row = $objPHPExcel->getActiveSheet()->getHighestRow()+1;

	$colK = 'A';
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