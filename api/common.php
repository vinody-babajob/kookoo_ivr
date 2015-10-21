<?php

require "../predis/autoload.php";
Predis\Autoloader::register();

$api_key = 'KKdaca8671027e1efec66026e87a8ce4f4';
$api_url = 'http://52.10.138.252';
$website_url = '';

function curlGet($url) {
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);

	$result = curl_exec($curl);

	if (!$result) {
		error_log('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
	}

	curl_close($curl);
}

function getRedisInstance() {
	$redis = null;

	try {
		$redis = new Predis\Client();

		// This connection is for a remote server
		/*
			$redis = new PredisClient(array(
			    "scheme" => "tcp",
			    "host" => "153.202.124.2",
			    "port" => 6379
			));
		*/
	}
	catch (Exception $e) {
		error_log($e->getMessage());
	}

	return $redis;
}


function doOutboundCall($from, $to) {
	global $api_key, $api_url;
	$call_url = 'http://www.kookoo.in/outbound/outbound.php?phone_no='. $from .'&api_key='. $api_key;
	$callback_url = 'http://' .$api_url. '/agentcustomerflow.php?custno=' . $to . '&agentid=' . $from;

	$call_url .= '&url=' . rawurlencode($callback_url);
	curlGet($call_url);
}

function nextCall($agentid) {

	$redis = getRedisInstance();

	if ($redis) {
		error_log("HasRedis");
		$agentavailable = $redis->get($agentid . '_state'); // get state of agent from redis

		if ($agentavailable) {
			error_log("agentavailable");

			if ($redis->llen($agentid . '_customer_queue') > 0) {
				error_log("people in queue");
				$nextcustomer_no = $redis->lpop($agentid . '_customer_queue'); // get next customer id redis
				$redis->set($agentid.'_currentcustomer', $nextcustomer_no);


				doOutboundCall($agentid, $nextcustomer_no);
			}
		}
	}

	
	
}

?>