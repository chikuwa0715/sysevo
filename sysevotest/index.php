<?php

require_once __DIR__ . '/vendor/autoload.php';
include_once "database.php";

$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => getenv('CHANNEL_SECRET')]);

$signature = $_SERVER["HTTP_" . \LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE];
try {
    $events = $bot->parseEventRequest(file_get_contents('php://input'), $signature);
} catch(\LINE\LINEBot\Exception\InvalidSignatureException $e) {
    error_log("parseEventRequest failed. InvalidSignatureException => ".var_export($e, true));
} catch(\LINE\LINEBot\Exception\UnknownEventTypeException $e) {
    error_log("parseEventRequest failed. UnknownEventTypeException => ".var_export($e, true));
} catch(\LINE\LINEBot\Exception\UnknownMessageTypeException $e) {
    error_log("parseEventRequest failed. UnknownMessageTypeException => ".var_export($e, true));
} catch(\LINE\LINEBot\Exception\InvalidEventRequestException $e) {
    error_log("parseEventRequest failed. InvalidEventRequestException => ".var_export($e, true));
}

foreach ($events as $event) {

    $profile = $bot->getProfile($event->getUserId())->getJSONDecodedBody();

    if (($event instanceof \LINE\LINEBot\Event\FollowEvent)) {
        error_log("Follow me !/" . $event->getUserId());
        registerFrends($event->getUserId(), $profile["displayName"], $profile['pictureUrl']);
    }

    if (($event instanceof \LINE\LINEBot\Event\UnfollowEvent)) {
        error_log("Unfollow me .../" . $event->getUserId());
        deleteFrends($event->getUserId());
    }

    if (!($event instanceof \LINE\LINEBot\Event\MessageEvent)) {
        error_log('Non message event has come');
        continue;
    }
    if (!($event instanceof \LINE\LINEBot\Event\MessageEvent\TextMessage)) {
        error_log('Non text message has come');
        continue;
    }
    $inputString = file_get_contents('php://input');

    // オウム返し
    //$bot->replyText($event->getReplyToken(), $event->getText());


    //$profile = $bot->getProfile($event->getUserId())->getJSONDecodedBody();
    //$message = $profile["displayName"] . "さん、おはようございます！今日も頑張りましょう！";
    //$bot->replyMessage($event->getReplyToken(),
    //  (new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder())
    //    ->add(new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($message))
    //    ->add(new \LINE\LINEBot\MessageBuilder\StickerMessageBuilder(1, 114))
    //);
  
    // 出題中テーブルより、出題中の問題IDを取得
    $info = getUserInfo($event->getUserId());
    // ユーザからのリクエストを取得
    $req = $event->getText();

    $builder = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
  
    // 出題中テーブルにユーザ情報が存在しない場合
    if($info === PDO::PARAM_NULL) {

        // 出題指示の場合
        if( strcmp($req, "問題") == 0 ){

            $message = $profile["displayName"] . "さん！" . chr(10) . "ようこそ情報処理一問一答ボットへ！" . chr(10) . "それでは問題を出題します。";

            // 問題IDをランダム抽出
            //$hash = array('H22-AT-39', 'H17-SP-53', 'H16-AT-27', 'H29-AT-34', 'H20-SP-79' );
            //$key = array_rand($hash);
            //error_log($hash[$key]);

            //$question = getQuestion($hash[$key]);
            //$choices = getChoices($hash[$key]);

//            $questid = getQuestid($event->getUserId(), getLevel($event->getUserId()));
            $questid = getQuestidEx($event->getUserId(), getLevel($event->getUserId()));

            if($questid === PDO::PARAM_NULL){

                // スタンプを設定
                $builder->add(new \LINE\LINEBot\MessageBuilder\StickerMessageBuilder(1, 3));
                // 応答メッセージを設定
                $builder->add(new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('全問クリアです・・・新しい問題が配信されるまで、しばらくお待ちください'));

            } else {

                $question = getQuestion($questid);
                $choices = getChoices($questid);

                // Welcomeメッセージを設定
                $builder->add(new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($message));
                // 問題文を設定
                $builder->add(new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($question));
                // 選択肢を設定
                $builder->add(new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($choices));

                // 出題中テーブルにユーザ情報を登録
                registerUser($event->getUserId(), $questid);
                // 出題履歴を登録
                setQuestHistory($event->getUserId(), $questid);

            }

        } elseif( strcmp($req, "設定") == 0 ) {

            $levels = array("", "FE", "AP");

            $bef = getLevel($event->getUserId());
            $aft = ($bef % 2) + 1;
            // [level=1]→(1 % 2) + 1→[level=2]
            // [level=2]→(2 % 2) + 1→[level=1]

            setLevel($event->getUserId(), $aft);

            $text = '設定を変更しました。' . chr(10) . '（変更前：' . $levels[$bef] . '／変更後：' . $levels[$aft] . '）';
            $builder->add(new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($text));

        } elseif( strcmp($req, "mypage") == 0 ) {

            $enc = getEnccode($event->getUserId());
            $text = 'https://sysevotest.herokuapp.com/mypage2018.php?id=' . $enc;
            $builder->add(new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($text));

        } else {

            $text = '問題を解く場合は、「問題」と発信してください。';
            $builder->add(new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($text));

        }

    } else {

        $ans = getAnswer($info);
        error_log('ユーザid[' . $event->getUserId() . ']／問題[' . $info . ']／回答[' . $req . ']／正解[' . $ans . ']');

        // 選択肢の範囲を判定
        if( strcmp($req, "ア") == 0 || strcmp($req, "イ") == 0 || strcmp($req, "ウ") == 0 || strcmp($req, "エ") == 0 ){

            $except = '';
            // 正解の場合
            if( strcmp($req, $ans) == 0 ){
                $text = '正解！';
                $ok_stkids = array(2,13,106,114,125);
                $stkid = $ok_stkids[array_rand($ok_stkids)];
                $result = 1;
                $except = $info;
            // 不正解の場合
            } else {
                $text = '不正解...';
                $ng_stkids = array(3,9,104,111,115,135);
                $stkid = $ng_stkids[array_rand($ng_stkids)];
                $result = 0;
            }

            // 回答結果を設定
            $builder->add(new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($text));
            // スタンプを設定
            $builder->add(new \LINE\LINEBot\MessageBuilder\StickerMessageBuilder(1, $stkid));
            // 解説を設定
            $builder->add(new \LINE\LINEBot\MessageBuilder\TextMessageBuilder(getComment($info)));

            // 出題中テーブルよりユーザ情報をクリア
            deleteUser($event->getUserId());
            // 回答履歴を登録
            setAnswerHistory($event->getUserId(), $info, $result);
            // 友だちテーブルのユーザ情報を更新
            updateFrends($event->getUserId(), $profile["displayName"], $profile['pictureUrl'], $except);

        } else {

            $text = '回答はア、イ、ウ、エ、よりお願いします。';
            $builder->add(new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($text));

        }

    }

    $response = $bot->replyMessage($event->getReplyToken(), $builder);
    if (!$response->isSucceeded()) {
        error_log('Failed!'. $response->getHTTPStatus . ' ' . $response->getRawBody());
    }
  
}

 ?>
