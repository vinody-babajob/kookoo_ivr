<?php

require dirname(__FILE__).'/KooKoo-PHP/kookoophp/response.php';//include response.php into your code
session_start();

$r = new response();
$r->setFiller(true);

if (isset($_REQUEST['event']) && $_REQUEST['event'] == 'NewCall') {
    

    $r->dial('8885208669');
    

} elseif (isset($_REQUEST['event']) && $_REQUEST['event'] == 'Dial') {
    if ($_REQUEST['status'] == 'answered') {
        $r->addPlayText("dialled number is answered");
    } else {
        $r->addPlayText("dialled number is not answered");
    }

    $cd = new CollectDtmf(); //initiate new collect dtmf object
    $cd->setMaxDigits(15);
    $cd->setTermChar('#');
    $cd->addPlayText("Please enter number to send message end with hash!");
    $r->addCollectDtmf($cd);
    $_SESSION['next_goto'] = 'phonemenu';

    //$r->addHangup();
}  elseif ($_SESSION['next_goto'] == 'phonemenu' && isset($_REQUEST['event']) && $_REQUEST['event'] == 'GotDTMF') {
    if (isset($_REQUEST['data']) && !empty($_REQUEST['data']) && strlen($_REQUEST['data']) >= 9) {
        

        $_SESSION['pref_num']=$_REQUEST['data'];

        $r->addPlayText('Please Record Your Message to send!');

        //give unique file name for each recording
		$r->addRecord('filename2','wav','120');

        $_SESSION['next_goto'] == 'Record_Status';

    } else {
        $r->addPlayText("you have not given any input please re enter or wrong input");
        
        $cd = new CollectDtmf(); //initiate new collect dtmf object
	    $cd->setMaxDigits(10);
	    $cd->setTermChar('#');
	    $cd->addPlayText("Please enter number to send message end with hash!");
	    $r->addCollectDtmf($cd);
    }
} else if($_REQUEST['event'] == 'Record' && $_SESSION['next_goto'] == 'Record_Status' ) {
//recorded file will be come as  url in data param
//print parameter data value
	 $r->addPlayText('your recorded audio is ');
	 $_SESSION['record_url']=$_REQUEST['data'];
	 $r->addPlayAudio($_SESSION['record_url']);
	 $r->addPlayText('Thanks you for calling, we will deliver your message');
} else if($_REQUEST['event'] == 'Dial' && $_SESSION['next_goto'] == 'Dial1_Status' ) {
	//dial url will come data param  //if dial record false then data value will be -1 or null
	//dial status will come in status (answered/not_answered) param
	//print parameter data and status params value
 	 $_SESSION['dial_record_url']=$_REQUEST['data'];
	 $_SESSION['dial_status']=$_REQUEST['status'];
	 $_SESSION['dial_callduration']=$_REQUEST['callduration'];
	 if($_REQUEST['status'] == 'not_answered'){
		//if you would like dial another number, if first call not answered,
		//	
	 	$r->addHangup();
	 }else{
	 	$r->addPlayAudio($_SESSION['record_url']);
	 	 $r->addPlayText('Thanks you for calling, ');
	 	 $r->addHangup();	// do something more or send hang up to kookoo
	// call is answered
	 }
	 
} else{
	$r->addHangup();
}

// $r->addPlayText("I Love Koo Koo"); // Play any text to play
// $r->addHangup();
$r->send();

?>