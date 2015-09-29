<?php
	/** Include PHPExcel */
require dirname(__FILE__).'/PHPExcel/Classes/PHPExcel.php';
require_once dirname(__FILE__).'/PHPExcel/Classes/PHPExcel/IOFactory.php';
require_once dirname(__FILE__).'/vendor/autoload.php';

$excel_path = dirname(__FILE__).'/downloads/';

define('GUSER', 'vinody@babajob.com'); // GMail username
define('GPWD', '8885208669'); // GMail password


function smtpmailer($to_array, $from, $from_name, $subject, $body, $is_gmail = true) { 
	global $error;
	$mail = new PHPMailer();
	$mail->IsSMTP();
	$mail->SMTPAuth = true;
	$mail->isHTML(true);

	if ($is_gmail) {
		$mail->SMTPSecure = 'tls'; 
		$mail->Host = 'smtp.gmail.com';
		$mail->Port = 587;  
		$mail->Username = GUSER;  
		$mail->Password = GPWD;   
	} else {
		// $mail->Host = SMTPSERVER;
		// $mail->Username = SMTPUSER;  
		// $mail->Password = SMTPPWD;
	}        
	$mail->SetFrom($from, $from_name);
	$mail->Subject = $subject;
	$mail->Body = $body;

	foreach ($to_array as $email) {
		$mail->AddAddress($email);
	}
	
	if(!$mail->Send()) {
		$error = 'Mail error: '.$mail->ErrorInfo;
		return false;
	} else {
		$error = 'Message sent!';
		return true;
	}
}

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

function sendFileUpdateMail($subject, $body) {
	$to = ['vinod.designer1@gmail.com'];//['krishnamoorthym@babajob.com','arpithag@babajob.com'];
	$from = 'vinody@babajob.com';
	$from_name = 'VinodY';

	smtpmailer($to, $from, $from_name, $subject, $body, $is_gmail = true);
}

function storeVoiceData($queryArray) {
	$filename = "voicemail.xlsx";
	storeDataToExcel($filename, $queryArray);

	$subject = 'A New VoiceMail';
	$body = 'Hey,<br>';
	$body .= 'New Voice Mail was added to excel sheet please check at http://52.10.138.252/downloads/' . $filename . '<br>';
	$body .= 'Regards,<br> Babajob IVR Team';

	sendFileUpdateMail($subject, $body);
}

function storeCallAttemptData($queryArray) {
	$filename = "callattempt.xlsx";
	storeDataToExcel($filename, $queryArray);

	$subject = 'A New Callattempt';
	$body = 'Hey,<br>';
	$body .= 'New call attempte was made. Please check excel sheet at http://52.10.138.252/downloads/' . $filename . '<br>';
	$body .= 'Regards,<br> Babajob IVR Team';

	sendFileUpdateMail($subject, $body);
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