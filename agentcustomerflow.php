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

$STAGE1 = 1;
$STAGE2 = 2;
$STAGE3 = 2;

$apiurl = 'http://52.10.138.252';
$customerNumber = $_REQUEST['custno'];
$agentid = $_REQUEST['agentid'];

function curlPost($url, $fields) {
    //url-ify the data for the POST
    foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
    rtrim($fields_string, '&');

    $ch = curl_init();

    //set the url, number of POST vars, POST data
    curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_POST, count($fields));
    curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

    //execute post
    $result = curl_exec($ch);

    //close connection
    curl_close($ch);
}

function sendCallDoneInfo($request, $stage) {
    global $apiurl;
    global $agentid;

    $callduration = getValueFromArray($request, 'callduration');
    $status = getValueFromArray($request, 'status');
    $recordurl = getValueFromArray($request, 'data');
    $message = getValueFromArray($request, 'message');

    $data = array(
        "callduration" => $callduration,
        "status" => $status,
        "recordurl" => $data,
        "message" => $message,
        "stage" => $stage,
        "agent" => $agentid,
        "type" => "nextcall"
    );

    curlPost($apiurl . '/api/agent.php', $data);
}

if (isset($_REQUEST['event']) && $_REQUEST['event'] == 'NewCall') {

    $r->addPlayText("Please wail while we connecting");
    $r->addDial($customerNumber, true); //phone number to dial
} elseif (isset($_REQUEST['event']) && $_REQUEST['event'] == 'Dial') {
    sendCallDoneInfo($_REQUEST, $STAGE1);

    if ($_REQUEST['status'] == 'answered') {
        //$r->addPlayText("dialled number is answered");
    } else {
        $r->addPlayText("dialled number is not answered");
    }
    $r->addHangup();
} elseif (isset($_REQUEST['event']) && $_REQUEST['event'] == 'Hangup') {

    if (isset($_REQUEST['process']) && $_REQUEST['process'] == 'dial') {
        sendCallDoneInfo($_REQUEST, $STAGE2);
    } else if (isset($_REQUEST['process']) && $_REQUEST['process'] == 'none') {
        sendCallInfo($_REQUEST, $STAGE3);
    }
} else {
    $r->addHangup();
}

$r->send();

?>