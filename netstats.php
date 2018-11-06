<?php
$api = 'https://pool.pennykoin.com/daemon';
if (isset($_GET['api'])) $api = $_GET['api'];
$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $api.'/getinfo');
$result = curl_exec($ch);
$obj = json_decode($result, TRUE);
curl_close($ch);
//print_r($obj);
$difficulty = $obj['difficulty'];
$hashrate = round($difficulty / 180);
//print_r($hashrate);
$whatget = $_GET['whatget'];
print_r($$whatget);
?>
