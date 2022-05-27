<?php
$data = json_decode(file_get_contents("../local_data/twitter_data.json"),true); // jsonファイルの読み込み（非ハードコード化）

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


$sig_param = rawurlencode('POST')."&".
  rawurlencode('https://api.twitter.com/1.1/direct_messages/events/new.json')."&".
  rawurlencode( http_build_query( $paramData , "", "&" ) );
//var_dump($sig_param);
//echo '<br>';
$signature = hash_hmac( "sha1", $sig_param, $signature_key, TRUE );
$signature = base64_encode( $signature );
$paramData['oauth_signature'] = $signature;

$httpHeader = [
    'Authorization: OAuth '.http_build_query( $paramData, "", "," ),
    'content-type: application/json'
];
//var_dump($httpHeader);
//echo '<br>';

$postData = [
    "event" => [
        "type" => "message_create",
        "message_create" => [
            "target" => [
                "recipient_id" => $data["recipient_id"]  //送信先ID
            ],
            "message_data" => [
                "text" => "Hello World!"  //送信したいメッセージ
            ]
        ]
    ]
];
//var_dump($postData);

$ch = curl_init();
curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);
curl_setopt($ch, CURLOPT_URL, 'https://api.twitter.com/1.1/direct_messages/events/new.json');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, rawurlencode('POST'));
//curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( $postData ));

var_dump(rawurlencode('POST'));
echo '<br>';
var_dump('https://api.twitter.com/1.1/direct_messages/events/new.json');
echo '<br>';
var_dump($httpHeader);
echo '<br>postData<br>';
var_dump(json_encode( $postData ));
echo '<br>';

$json = curl_exec($ch);
curl_close($ch);

$response = json_decode( $json, true );
echo '<br>';
var_dump( $response );