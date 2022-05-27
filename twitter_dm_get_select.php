<?php
$user_id="115639376"; // 表示するユーザーIDを指定（ローソンのIDを指定）
$get_num=3; // 表示するDMの数を指定
echo "表示するユーザーID: $user_id <br>";
echo "表示するDMの数: $get_num <br><br>";

$data = json_decode(file_get_contents("../private_data/twitter_data.json"),true); // プライベートデータの読み込み（非ハードコード化）

// パラメータの設定
$signature_key = rawurlencode( $data["api_secret"] )."&".rawurlencode( $data["access_token_secret"] );
$paramData = [
    "oauth_token" => rawurlencode( $data["access_token"] ),
    "oauth_consumer_key" => rawurlencode( $data["api_key"] ),
    "oauth_signature_method" => rawurlencode( "HMAC-SHA1" ),
    "oauth_timestamp" => time(),
    "oauth_nonce" => rawurlencode(microtime()), // rawurlencode()でエンコードする必要あり
    "oauth_version" => rawurlencode( "1.0" )
];
ksort( $paramData ); // パラメータをソート

$url = "https://api.twitter.com/1.1/direct_messages/events/list.json"; // DMのリストを取得するAPIのURL

// 署名キーとパラメータから署名を作る
$sig_param = rawurlencode('GET')."&".
  rawurlencode($url)."&".
  rawurlencode( http_build_query( $paramData , "", "&" ) );
$signature = hash_hmac( "sha1", $sig_param, $signature_key, TRUE );
$signature = base64_encode( $signature );
$paramData['oauth_signature'] = $signature;

// パラメータからHTTPヘッダ用の配列を作る
$httpHeader = [
  'Authorization: OAuth '.http_build_query( $paramData, "", "," ),
  'content-type: application/json'
];

// cURLで取得
$ch = curl_init();
curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, rawurlencode('GET'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$json = curl_exec($ch);
curl_close($ch);

// エンドポイントの応答を受け取る
$response = json_decode($json, true);
$events=$response['events'];

// 指定のDMのみを抽出
$counter = 0;
$dm_message=[]; // メッセージを格納する配列
foreach($events as $event){
  if($user_id===$event['message_create']['sender_id']){
    $counter++;
    $text=$event['message_create']['message_data']['text'];
    echo "メッセージ $counter :<br> $text <br><br>";
    $dm_message[]=$text;
  }
  if($counter>=$get_num){
    break;
  }
}
if($counter<$get_num){
  echo "これ以上 $user_id からのメッセージは見つかりませんでした。<br><br>";
}
echo "dm_messageの中身:<br>";
var_dump( $dm_message );
