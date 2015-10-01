<?php

require dirname(__FILE__).'/KooKoo-PHP/kookoophp/response.php';//include response.php into your code
session_start();

$r = new response();
$r->setFiller(true);

if (isset($_REQUEST['event']) && $_REQUEST['event'] == 'NewCall') {
	$r->addConference("1234");
}

$r->send();