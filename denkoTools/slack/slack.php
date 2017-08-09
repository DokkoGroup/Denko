<?php


function sendSlack($url, $username, $icon, $channel, $text){
	$data = array();
	$data['username']=$username;
	$data['icon_emoji']=$icon;
	$data['channel']=$channel;
	$data['text']=$text;
	$payload = json_encode($data);
	$opts = array('http' =>
		array(
			'method'  => 'POST',
			'header'  => 'Content-type: application/x-www-form-urlencoded',
			'content' => http_build_query(array('payload' => $payload))
		)
	);
	$context  = stream_context_create($opts);
	$result = file_get_contents($url, false, $context);

}

// Example:
//$url='https://hooks.slack.com/services/T3XXXXX1B/B5WG55XXXXXXXX8g7ECwtNgsQ9Mf4HAkjm';
//sendSlack($url,'DokkoMonitor',':dokkomonitor:','@egeringer',"Hello Ezequiel, I'm dokkoMonitor!");
//sendSlack($url,'DokkoMonitor',':dokkomonitor:','werbung',"Hello Channel Werbung, I'm dokkoMonitor!");