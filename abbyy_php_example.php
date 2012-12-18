<?php

require_once(dirname(__FILE__) . '/config.inc.php');

// 1. Send image to Cloud OCR SDK using processImage call
// 2.	Get response as xml
// 3.	Read taskId from xml

// Image to process
$fileName = 'liangqibaxingdonnew06chen_0054.jpg';
$fileName = 'notesfromleydenm01rijk_0119.jpg';
$fileName = '0181.png';

$filePath = dirname(__FILE__) . '/images/' . $fileName;

// Output format
$format = 'pdfSearchable';

// Extensions for outpu formats
$extension = array(
'pdfSearchable' => 'pdf',
'rtf' => 'rtf',
'xml' => 'xml'
);

// Language to recognise
$language = "ChinesePRC,English";
$language = "English";

// Output file name
$parts = pathinfo($fileName);
$outputFileName = $parts['filename'] . '.' . $extension[$format];
 
  
// API  
$url = 'http://cloud.ocrsdk.com/processImage?language=' . $langauge . '&exportFormat=' . $format;

// Send HTTP POST request and ret xml response
$curlHandle = curl_init();
curl_setopt($curlHandle, CURLOPT_URL, $url);

// HTTP proxy if needed
if ($config['proxy_name'] != '')
{
	curl_setopt($curlHandle, CURLOPT_PROXY, $config['proxy_name'] . ':' . $config['proxy_port']);
}
  
// Set HTTP headers
$headers = array();

// Override Expect: 100-continue header (may cause problems with HTTP proxies
// http://the-stickman.com/web-development/php-and-curl-disabling-100-continue-header/
$headers[] = 'Expect:'; 
curl_setopt ($curlHandle, CURLOPT_HTTPHEADER, $headers);

curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curlHandle, CURLOPT_USERPWD, $config['abbyy_applicationId'] . ':' . $config['abbyy_password']);
curl_setopt($curlHandle, CURLOPT_POST, 1);
$post_array = array(
  "my_file"=>"@".$filePath,
);
curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $post_array);
  
$response = curl_exec($curlHandle);
if($response == FALSE) 
{
	$errorText = curl_error($curlHandle);
	curl_close($curlHandle);
	die($errorText);
}
curl_close($curlHandle);
  

// Parse xml response
$xml = simplexml_load_string($response);
$arr = $xml->task[0]->attributes();

// Task id
$taskid = $arr["id"];  

// 4. Get task information in a loop until task processing finishes
// 5. If response contains "Completed" staus - extract url with result
// 6. Download recognition result (text) and display it

$url = 'http://cloud.ocrsdk.com/getTaskStatus';
$qry_str = "?taskid=$taskid";

// Check task status in a loop until it is finished
// TODO: support states indicating error
do
{
	sleep(5);
	echo ".";
	$curlHandle = curl_init();
	
	// HTTP proxy if needed
	if ($config['proxy_name'] != '')
	{
		curl_setopt($curlHandle, CURLOPT_PROXY, $config['proxy_name'] . ':' . $config['proxy_port']);
	}	
	
	curl_setopt($curlHandle, CURLOPT_URL, $url.$qry_str);
	curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curlHandle, CURLOPT_USERPWD, $config['abbyy_applicationId'] . ':' . $config['abbyy_password']);
	$response = curl_exec($curlHandle);
	curl_close($curlHandle);
	
	// parse xml
	$xml = simplexml_load_string($response);
	$arr = $xml->task[0]->attributes();
}
while($arr["status"] != "Completed");
  
echo "\n";

  // Result is ready. Download it
$url = $arr["resultUrl"];   
$curlHandle = curl_init();
curl_setopt($curlHandle, CURLOPT_URL, $url);
  
// HTTP proxy if needed
if ($config['proxy_name'] != '')
{
	curl_setopt($curlHandle, CURLOPT_PROXY, $config['proxy_name'] . ':' . $config['proxy_port']);
}	  

curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
// Warning! This is for easier out-of-the box usage of the sample only.
// The URL to the result has https:// prefix, so SSL is required to
// download from it. For whatever reason PHP runtime fails to perform
// a request unless SSL certificate verification is off.
curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($curlHandle);
curl_close($curlHandle);

file_put_contents($outputFileName, $response);

?>