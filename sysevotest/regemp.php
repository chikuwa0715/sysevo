<!DOCTYPE html>
<html>
<head>
<title>社員情報の登録結果</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php
    include_once "database.php";

    //遷移元の画面を保持
    $uri = $_SERVER['HTTP_REFERER'];

    if($_POST['action']){
        //ユーザid（hidden）を取得
        $userid = htmlspecialchars($_POST['userid']);
        //社員番号を取得
        $emp_no = htmlspecialchars($_POST['emp_no']);
        //氏名を取得
        $emp_name = htmlspecialchars($_POST['emp_name']);

        //社員情報を登録
        setEmpInfo($userid, $emp_no, $emp_name);

        //遷移元の画面に戻る
        //header("Location: ".$uri);
    }
    echo "<meta http-equiv=\"refresh\" content=\"10; URL=" . $uri . "\">" . PHP_EOL ;
?>
</head>
<body>
<p>登録ありがとうございます。</p>
<p>そのままお待ちください。（10秒後にMyPageに戻ります）</p>
</body>
</html>
