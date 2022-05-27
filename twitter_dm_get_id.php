<?php
$data = json_decode(file_get_contents("../local_data/twitter_data.json"),true); // jsonファイルの読み込み（非ハードコード化）

$signature_key = rawurlencode( $data["api_secret"] )."&".rawurlencode( $data["access_token_secret"] );

$base_url='https://api.twitter.com/1.1/direct_messages/events/show.json';
$queryData = ["id"=>rawurlencode($data['message_id'])];

$paramData = [
    "oauth_token" => rawurlencode( $data["access_token"] ),
    "oauth_consumer_key" => rawurlencode( $data["api_key"] ),
    "oauth_signature_method" => rawurlencode( "HMAC-SHA1" ),
    "oauth_timestamp" => time(),
    "oauth_nonce" => rawurlencode(microtime()), // rawurlencode()でエンコードする必要あり
    //"oauth_nonce" => microtime(),
    "oauth_version" => rawurlencode( "1.0" ),
];
$paramData=array_merge($paramData,$queryData);
ksort( $paramData );

$url = $base_url.'?'.http_build_query( $queryData , "", "&" );
echo '<br>url: ';
var_dump($url);

$sig_param = rawurlencode('GET')."&".
  rawurlencode($base_url)."&".
  rawurlencode( http_build_query( $paramData , "", "&" ) );

echo '<br><br>';
var_dump($sig_param);

$signature = hash_hmac( "sha1", $sig_param, $signature_key, TRUE );
$signature = base64_encode( $signature );
$paramData['oauth_signature'] = $signature;

$httpHeader = [
    'Authorization: OAuth '.http_build_query( $paramData, "", "," ),
    'content-type: application/json'
];

echo '<br><br>';
var_dump($httpHeader);

$ch = curl_init();
curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, rawurlencode('GET'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$json = curl_exec($ch);
$response = json_decode($json, true);
curl_close($ch);

echo '<br><br><br>';
var_dump( $response );
echo '<br><br>';
/*
$events=$response['events'];
foreach($events as $event){
    //$name = $result['includes']['users'][$i]['name'];
    $user_id = $event['message_create']['sender_id'];
    $text = $event['message_create']['message_data']['text'];
    echo '<p>';
    echo 'ユーザID：'.$user_id.', メッセージ: ';
    echo $text;
    echo '</p>';
  }
*/