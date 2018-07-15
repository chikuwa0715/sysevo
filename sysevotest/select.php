<?php
include_once "database.php";

//https://sysevotest.herokuapp.com/select.php
//curl -X POST -d "Februry 03, 2018 at 08:00AM" https://sysevotest.herokuapp.com/select.php

// Composerでインストールしたライブラリを一括読み込み
require_once __DIR__ . '/vendor/autoload.php';

// アクセストークンを使いCurlHTTPClientをインスタンス化
$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
// CurlHTTPClientとシークレットを使いLINEBotをインスタンス化
$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => getenv('CHANNEL_SECRET')]);

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
      $hour = '15';
      break;

    case $hour === '06' && $ampm === 'PM':
      $message = '定期配信の時間です！[18:00]';
      $hour = '18';
      break;

    case $hour === '09' && $ampm === 'PM':
      $message = '定期配信の時間です！[21:00]';
      $hour = '21';
      break;

    default:
      $message = '';

}

if(empty($message)){

    error_log('時間外なので配信をスキップします');

} else {
    exec('nohup php ./async_main.php ' . $hour . '> /dev/null &');

//    $dbh = dbConnection::getConnection();
//    $sql = 'select pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') from '. T_FRIENDS;
//    $sth = $dbh->prepare($sql);
//    $sth->execute();
//
//    // 友だちリストから、ユーザIDを取得
//    while( $row = $sth->fetch(PDO::FETCH_BOTH) ){
//
//        //FETCH_ASSOC 列名
//        //FETCH_NUM   添え字
//        //FETCH_BOTH  添え字or列名
//        error_log('fetch0: ' . $row[0]);
//        var_dump($row[0]);
//
//        // ユーザIDを取得
//        $userId = $row[0];
//
//        // 出題中テーブルよりユーザ情報をクリア
//        deleteUser($userId);
//
//        // 問題IDをランダム抽出
//        //$hash = array('H22-AT-39', 'H17-SP-53', 'H16-AT-27', 'H29-AT-34', 'H20-SP-79' );
//        //$key = array_rand($hash);
//        //error_log($hash[$key]);
//
//        //$question = getQuestion($hash[$key]);
//        //$choices = getChoices($hash[$key]);
//
//        $questid = getQuestid($userId, getLevel($userId));
//        error_log($questid);
//
//        $builder = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
//
//        if($questid === PDO::PARAM_NULL){
//
//            // 配信メッセージを設定
//            $builder->add(new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($message));
//            // スタンプを設定
//            $builder->add(new \LINE\LINEBot\MessageBuilder\StickerMessageBuilder(1, 3));
//            // 応答メッセージを設定
//            $builder->add(new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('全問クリアです・・・新しい問題が出来るまでお待ちください'));
//
//        } else {
//
//            $question = getQuestion($questid);
//            $choices = getChoices($questid);
//
//            // 配信メッセージを設定
//            $builder->add(new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($message));
//            // 問題文を設定
//            $builder->add(new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($question));
//            // 選択肢を設定
//            $builder->add(new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($choices));
//
//            // 出題中テーブルにユーザ情報を登録
//            registerUser($userId, $questid);
//            // 出題履歴を登録
//            setQuestHistory($userId, $questid);
//
//        }
//
//        // 問題をユーザID宛にプッシュ
//        $response = $bot->pushMessage($userId, $builder);
//        if (!$response->isSucceeded()) {
//            error_log('Failed!'. $response->getHTTPStatus . ' ' . $response->getRawBody());
//        }
//    }
}

?>
