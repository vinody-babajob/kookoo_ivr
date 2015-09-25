<?php

require dirname(__FILE__).'/KooKoo-PHP/kookoophp/response.php';//include response.php into your code

$r = new response();
$r->setFiller(true);

if (isset($_REQUEST['event']) && $_REQUEST['event'] == 'NewCall') {
    $cd = new CollectDtmf(); //initiate new collect dtmf object
    $cd->addPlayText("Press 1, for sales");
    $cd->addPlayText("Press 2, for to know about our company");
    $r->addCollectDtmf($cd);
} elseif (isset($_REQUEST['event']) && $_REQUEST['event'] == 'GotDTMF') {
    if (isset($_REQUEST['data']) && !empty($_REQUEST['data'])) {
        $r->addPlayText("you have pressed D T M F" . $_REQUEST['data']);
    } else {
        $r->addPlayText("you have not given any input please re enter");
        $cd = new CollectDtmf(); //initiate new collect dtmf object
        $cd->addPlayText("Press 1, for sales");
        $cd->addPlayText("Press 2, for to know about our company");
        $r->addCollectDtmf($cd);
    }
}else{
	$r->addHangup();
}

// $r->addPlayText("I Love Koo Koo"); // Play any text to play
// $r->addHangup();
$r->send();

?>