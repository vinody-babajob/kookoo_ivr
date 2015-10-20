<?php

require 'common.php';

switch ($_SERVER['REQUEST_METHOD']) {
	case "POST":
		managePost();
		break;
	case "PUT":
		break;
	case "GET":
		break;
	case "DELETE":
		break;
	default:
		break;
}

function nextAgentCall($data) {
	$callduration = $_POST["callduration"];
	$status = $_POST["status"];
	$recordurl = $_POST["recordurl"];
	$message = $_POST["message"];
	$stage = $_POST["stage"];
    $agent = $_POST["agent"];

    nextCall($agent);
}

function managePost() {
	$type = $_POST["type"];

	if ($type == "nextcall") {
		nextAgentCall($_POST);
	} else if ($type == "agentstate") {
		$redis = $getRedisInstance();

		if ($redis) {
			$agentid = $_POST["agent"];
			$agentstate = $_POST["state"];

			if ($agentstate == "true") $agentstate = true;
			else $agentstate = false;

			$redis->set($agentid . '_state', $agentstate);

			nextCall($agentid); // modify logic to not call always
		}
	} else if ($type == "assignagent") {
		$agentId = $_POST["agent"];

		$customers = $_POST["customers"];

		$customers = explode(',',$customers,0);

		$redis = $getRedisInstance();

		if ($redis) {
			for ($customers as $customer )
				$redis->rpush($agentid.'_customer_queue', $customer);
		}
	}

	http_response_code(200);
}


?>