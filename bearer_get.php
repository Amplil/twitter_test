<?php
$data = json_decode(file_get_contents("../local_data/twitter_data.json"),true); // jsonファイルの読み込み（非ハードコード化）
// エンドポイントを指定
$base_url = 'https://api.twitter.com/2/tweets/search/recent';

// 検索条件を指定
$query = [
  'query' => 'プログラミング PHP',
  'sort_order' => 'recency',
  'expansions' => 'author_id',
  'user.fields' => 'name,username'
];
$url = $base_url .'?'. http_build_query($query);

// ヘッダ生成
$token = $data['token'];  //Bearer Token
//var_dump($token);
$header = [
  'Authorization: Bearer ' . $token,
  'Content-Type: application/json',
];

//cURLで問い合わせ
$curl = curl_init();

curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($curl);

$result = json_decode($response, true);

curl_close($curl);

//結果のうち最新3件を出力
for ($i = 0; $i < 3; $i++) {
  $name = $result['includes']['users'][$i]['name'];
  $username = $result['includes']['users'][$i]['username'];
  $text = $result['data'][$i]['text'];
  echo '<p>';
  echo '投稿者：' . $name . '（@' . $username . '）<br>';
  echo $text;
  echo '</p>';
}