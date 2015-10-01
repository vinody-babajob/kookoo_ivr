<?php

require dirname(__FILE__).'/KooKoo-PHP/kookoophp/response.php';//include response.php into your code
session_start();

$r = new response();
$r->setFiller(true);

$job_queue = ['Sales', 'Delivery boy', 'Beautician'];
$job_locations =  array(
	'Sales' => ['Banglore', 'Chennai', 'Delhi'],
	'Delivery boy' => ['Banglore', 'Chennai', 'Delhi'],
	'Beautician' => ['Banglore', 'Chennai', 'Delhi']
);

$api_key = 'KKdaca8671027e1efec66026e87a8ce4f4';


function curlGet($url) {
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);

	$result = curl_exec($curl);

	if (!$result) {
		error_log('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
	}

	curl_close($curl);
}

function sendSMS($no, $message) {
	global $api_key;
	$sms_url = 'http://www.kookoo.in/outbound/outbound_sms.php?phone_no='. $no .'&api_key='. $api_key .'&message='. $message;
	curlGet($sms_url);
}


function checkIfValue($array, $key, $value) {
	if (isset($array[$key]) && $array[$key] == $value)
		return true;
	return false;
}

function getJobDTMF($cat, $location, $wrong=false) {
	$cd = new CollectDtmf(); //initiate new collect dtmf object
	if ($wrong)
		$cd->addPlayText("Please select right option!");
	$cd->addPlayText("We have an opening for ". $cat ." job in ". $location);
    $cd->addPlayText("Press 1, to hear same job but in different place");
    $cd->addPlayText("Press 2, to hear about different job in different place");
    $cd->addPlayText("Press 3, to hear next job");
    $cd->setMaxDigits(1);

    return $cd;
}

function noLocationsDTMF() {
	$cd = new CollectDtmf(); //initiate new collect dtmf object
	$cd->addPlayText("We no more location for this job!");
    $cd->addPlayText("Press 1, to hear about different job");
    $cd->addPlayText("Press 9, to record your own cv");
    $cd->setMaxDigits(1);

    return $cd;
}

function noCatDTMF() {
	$cd = new CollectDtmf(); //initiate new collect dtmf object
	$cd->addPlayText("We no more location for this job!");
    $cd->addPlayText("Press 1, if you didn't find your job in list");
    $cd->addPlayText("Press 9, to record your own cv");
    $cd->setMaxDigits(1);

    return $cd;
}


function sameJbDiffLoc() {
	global $_SESSION, $job_queue, $job_locations, $r;

	$exculded_locations = &$_SESSION['exculded_locations'];

	$cat_idx = $_SESSION['cat'];
	$loc_idx = $_SESSION['location'];

	$cat = $job_queue[ $cat_idx ];

	$exculded_locations[$cat][] = $job_locations[ $cat ][ $loc_idx ];

	if ( sizeof( $job_locations[$cat] ) > $loc_idx + 1 ) {
		$_SESSION['location'] = $loc_idx + 1;
		
		$loc = $job_locations[ $cat ][ $_SESSION['location'] ];

		$cd = getJobDTMF($cat, $loc);
    	$r->addCollectDtmf($cd);

    	$_SESSION['next_goto'] = 'job_1';
	} else {
		$cd = noLocationsDTMF();
		$r->addCollectDtmf($cd);

		$_SESSION['next_goto'] = 'nolocation';
	}
}

function diffJbDiffLoc($diffloc = true) {
	global $_SESSION, $job_queue, $job_locations, $r;

	$exculded_locations = &$_SESSION['exculded_locations'];

	$cat_idx = $_SESSION['cat'];
	$loc_idx = $_SESSION['location'];

	$loc = $job_locations[$cat][$loc_idx];

	if ( sizeof( $job_queue ) > $cat_idx + 1 ) {
		$_SESSION['cat'] = $cat_idx + 1;

		$cat = $job_queue[ $_SESSION['cat'] ];

		if ($diffloc)
			$exculded_locations[$cat][] = $loc;

		$loc_idx = 0;

		$loc = $job_locations[ $cat ][ $loc_idx ];

		while (
			sizeof($exculded_locations[$cat]) > 0
			&& in_array($loc, $exculded_locations[$cat])
			&& sizeof( $job_locations[$cat] ) > $loc_idx + 1 
		) {
			$loc_idx++;
			$loc = $job_locations[ $cat ][ $loc_idx ];
		}

		if (sizeof( $job_locations[$cat] ) > $loc_idx) {
			$_SESSION['location'] = $loc_idx;
			
			$loc = $job_locations[ $cat ][ $_SESSION['location'] ];

			$cd = getJobDTMF($cat, $loc);
	    	$r->addCollectDtmf($cd);

	    	$_SESSION['next_goto'] = 'job_1';
		} else {
			$cd = noLocationsDTMF();
			$r->addCollectDtmf($cd);

			$_SESSION['next_goto'] = 'nolocation';
		}
	} else {
		$cd = noCatDTMF();
		$r->addCollectDtmf($cd);

		$_SESSION['next_goto'] = 'nocat';
	}
}


function press1DTMF() {
	$cd = new CollectDtmf(); //initiate new collect dtmf object
	$cd->addPlayText("We will send you and sms for this job, please check within 5 minutes");
    $cd->addPlayText("Press 1, to hear more jobs like this");
    $cd->addPlayText("Press 2, to hear same job but in different place");
    $cd->addPlayText("Press 3, to hear about different job in different place");
    $cd->addPlayText("Press 4, to change salary");
    $cd->setMaxDigits(1);

    return $cd;
}

if (checkIfValue($_REQUEST, 'event', 'NewCall')) {

	$_SESSION['exculded_locations'] = array(
			'Sales' => [],
			'Delivery boy' => [],
			'Beautician' => [] 
		);

	$number = $_REQUEST['cid'];

	$_SESSION['cat'] = 0;
	$_SESSION['location'] = 0;
	$_SESSION['fromno'] = $number;
	$cat = $job_queue[ $_SESSION['cat'] ];
	$loc = $job_locations[ $cat ][ $_SESSION['location'] ];

	$cd = getJobDTMF($cat, $loc);
    $r->addCollectDtmf($cd);

    $_SESSION['next_goto'] = 'job_1';
} else if (checkIfValue($_REQUEST, 'event', 'GotDTMF') && checkIfValue($_SESSION, 'next_goto', 'job_1')) {
	$option = $_REQUEST['data'];
	$option = $option[0];

	if ($option == '1') {
		$cd = press1DTMF();
    	$r->addCollectDtmf($cd);

    	$_SESSION['next_goto'] = 'job_1_op1';
	} else if ($option == '2') {
		  sameJbDiffLoc();
	} else if ($option == '3') {
		diffJbDiffLoc();
	} else {
		$cat = $job_queue[ $_SESSION['cat'] ];
		$loc = $job_locations[ $cat ][ $_SESSION['location'] ];

		$cd = getJobDTMF($cat, $loc, true);
	    $r->addCollectDtmf($cd);

	    $_SESSION['next_goto'] = 'job_1';
	}

} else if (checkIfValue($_REQUEST, 'event', 'GotDTMF') && checkIfValue($_SESSION, 'next_goto', 'job_1_op1')) {
	$option = $_REQUEST['data'];
	$option = $option[0];
	$number = $_SESSION['fromno'];

	if ($option == '1') {
		// TODO send sms about similar jobs
		sendSMS($number, 'Similar Jobs');
		$r->addHangup();
	} else if ($option == '2') {
		// TODO CITY MESSAGE
		sendSMS($number, 'City message');
		$r->addHangup();
	} else if ($option == '3') {
		diffJbDiffLoc(false);
	} else if ($option == '4') {
		// TODO SALARY MESSAGE
		sendSMS($number, 'Salary message');
		$r->addHangup();
	} else {
		$r->addHangup();
	}

} else if (checkIfValue($_REQUEST, 'event', 'GotDTMF') && checkIfValue($_SESSION, 'next_goto', 'nolocation')) {
	$option = $_REQUEST['data'];
	$option = $option[0];
	$number = $_SESSION['fromno'];

	if ($option == '1') {
		// send location job
		sendSMS($number, 'location sms');
		$r->addHangup();
	} else if ($option == '9') {
		$r->addPlayText('Please Record Your CV to send and press # after finishing your record!');

	    //give unique file name for each recording
		$r->addRecord('user1','wav','120');

	    $_SESSION['next_goto'] = 'Record_Status';
	} else {
		$r->addHangup();
	}
} else if (checkIfValue($_REQUEST, 'event', 'GotDTMF') && checkIfValue($_SESSION, 'next_goto', 'nocat')) {
	$option = $_REQUEST['data'];
	$option = $option[0];
	$number = $_SESSION['fromno'];

	if ($option == '1') {
		// send sms for job list
		sendSMS($number, 'job list sms');
		$r->addHangup();
	} else if ($option == '9') {
		$r->addPlayText('Please Record Your CV to send and press # after finishing your record!');

	    //give unique file name for each recording
		$r->addRecord('user1','wav','120');

	    $_SESSION['next_goto'] = 'Record_Status';
	} else {
		$r->addHangup();
	}
} else if( checkIfValue($_REQUEST, 'event', 'Record') && checkIfValue($_SESSION, 'next_goto', 'Record_Status') ) {
	$r->addPlayText('your recorded audio is ');
	$_SESSION['record_url']=$_REQUEST['data'];
	error_log($_SESSION['record_url']);
	$r->addPlayAudio($_SESSION['record_url']);
	$r->addPlayText('Thanks you for calling, we will find your job');
} else {
	$r->addHangup();
}

$r->send();