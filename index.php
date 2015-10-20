<?php

require dirname(__FILE__).'/KooKoo-PHP/kookoophp/response.php';//include response.php into your code
session_start();

$r = new response();
$r->setFiller(true);

function getValueFromArray($arr, $key) {
	if (isset($arr[$key]))
		return $arr[$key];

	return '';
}

function sendCallDoneInfo($request) {
	$callduration = getValueFromArray($request, 'callduration');
	$status = getValueFromArray($request, 'status');
	$data = getValueFromArray($request, 'data');
	$message = getValueFromArray($request, 'message');
}

if (isset($_REQUEST['event']) && $_REQUEST['event'] == 'NewCall') {

	$r->addPlayText("Please wail while we connecting");
	$r->addDial($customerNumber, true); //phone number to dial
} elseif (isset($_REQUEST['event']) && $_REQUEST['event'] == 'Dial') {
	sendCallDoneInfo($_REQUEST);

    if ($_REQUEST['status'] == 'answered') {
        $r->addPlayText("dialled number is answered");
    } else {
        $r->addPlayText("dialled number is not answered");
    }
    $r->addHangup();
} elseif (isset($_REQUEST['event']) && $_REQUEST['event'] == 'Hangup') {

    if (isset($_REQUEST['process']) && $_REQUEST['process'] == 'dial') {
    	sendCallDoneInfo($_REQUEST);
    } else if (isset($_REQUEST['process']) && $_REQUEST['process'] == 'none') {
    	sendCallInfo($_REQUEST);
    }
} else {
    $r->addHangup();
}

$r->send();