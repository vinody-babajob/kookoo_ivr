<?php

require dirname(__FILE__).'/KooKoo-PHP/kookoophp/response.php';//include response.php into your code
session_start();

$r = new response();
$r->setFiller(true);


function check_if_value($array, $key, $value) {
	if (isset($array[$key]) && $array[$key] == $value)
		return true;
	return false;
}

if (check_if_value($_REQUEST, 'event', 'NewCall')) {
	$cd = new CollectDtmf(); //initiate new collect dtmf object
	$cd->addPlayText("We have an opening for sales job in banglore");
    $cd->addPlayText("Press 1, to hear same job but in different place");
    $cd->addPlayText("Press 2, to hear about different job in different place");
    $cd->addPlayText("Press 3, to hear next job");
    $cd->setMaxDigits(1);
    $r->addCollectDtmf($cd);

    $_SESSION['next_goto'] = 'job_1';
} else if (check_if_value($_REQUEST, 'event', 'GotDTMF') && check_if_value($_SESSION, 'next_goto', 'job_1')) {
	$option = $_REQUEST['data'];

	error_log(gettype($option));
} else {
	$r->addHangup();
}

$r->send();