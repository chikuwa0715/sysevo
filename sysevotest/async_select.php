<?php
include_once "database.php";

//curl -X POST -d "Februry 03, 2018 at 08:00AM" https://sysevotest.herokuapp.com/async_select.php

// Composerでインストールしたライブラリを一括読み込み
require_once __DIR__ . '/vendor/autoload.php';

// アクセストークンを使いCurlHTTPClientをインスタンス化
$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
// CurlHTTPClientとシークレットを使いLINEBotをインスタンス化
$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => getenv('CHANNEL_SECRET')]);

error_log('START');

$inputString = file_get_contents('php://input');
error_log($inputString);

// パラメータを分解
list($month, $day, $year, $at, $time) = explode(" ", $inputString);
$hour = substr($time,0,2);
$ampm = substr($time,5,2);

$message = '';
switch (true) {

    case $hour === '08' && $ampm === 'AM':
      $message = '定期配信の時間です！[08:00]';
      break;

    case $hour === '12' && $ampm === 'PM':
      $message = '定期配信の時間です！[12:00]';
      break;

    case $hour === '03' && $ampm === 'PM':
      $message = '定期配信の時間です！[15:00]';
      break;

    case $hour === '06' && $ampm === 'PM':
      $message = '定期配信の時間です！[18:00]';
      break;

    case $hour === '10' && $ampm === 'PM':
      $message = '定期配信の時間です！[22:00]';
      break;

    default:
      $message = '';

}

if(empty($message)){

    error_log('時間外なので配信をスキップします');

} else {
    exec('php ./async_main.php > /dev/null &');
}

error_log('END');

?>
