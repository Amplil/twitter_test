<?php
$data = json_decode(file_get_contents("../local_data/twitter_data.json"),true); // jsonファイルの読み込み（非ハードコード化）
//$base_url='https://api.twitter.com/1.1/direct_messages.json';

$signature_key = rawurlencode( $data["api_secret"] )."&".rawurlencode( $data["access_token_secret"] );

$paramData = [
    "oauth_token" => rawurlencode( $data["access_token"] ),
    "oauth_consumer_key" => rawurlencode( $data["api_key"] ),
    "oauth_signature_method" => rawurlencode( "HMAC-SHA1" ),
    "oauth_timestamp" => time(),
    "oauth_nonce" => rawurlencode(microtime()), // rawurlencode()でエンコードする必要あり
    //"oauth_nonce" => microtime(),
    "oauth_version" => rawurlencode( "1.0" )
];
ksort( $paramData );

//$url = "https://api.twitter.com/1.1/direct_messages.json?count=3";  
$url = "https://api.twitter.com/1.1/direct_messages/events/list.json";  

$sig_param = rawurlencode('GET')."&".
  //rawurlencode('https://api.twitter.com/1.1/direct_messages/events/list.json')."&".
  rawurlencode($url)."&".
  rawurlencode( http_build_query( $paramData , "", "&" ) );

//echo '<br>';
//var_dump($sig_param);

$signature = hash_hmac( "sha1", $sig_param, $signature_key, TRUE );
$signature = base64_encode( $signature );
$paramData['oauth_signature'] = $signature;
ksort( $paramData );

$httpHeader = [
    'Authorization: OAuth '.http_build_query( $paramData, "", "," ),
    'content-type: application/json'
];
//$httpHeader = 'Authorization: OAuth '.http_build_query( $paramData, "", "," );
//var_dump($httpHeader);
//echo '<br>';

$ch = curl_init();
curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);
//curl_setopt($ch, CURLOPT_URL, 'https://api.twitter.com/1.1/direct_messages/events/list.json');
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, rawurlencode('GET'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

//echo '<br>httpHeader: ';
//var_dump($httpHeader);

$json = curl_exec($ch);
$response = json_decode($json, true);
curl_close($ch);

//echo '<br>';
var_dump( $response );
echo '<br><br>';

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