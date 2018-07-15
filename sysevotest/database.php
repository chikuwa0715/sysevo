<?php

// テーブル名を定義
define('T_FRIENDS', 'friends');
define('T_LOGIN_USER', 'login_user');
define('T_QUEST', 'questions');
define('T_Q_HISTORY', 'question_history');
define('T_A_HISTORY', 'answer_history');
define('T_SCHEDULE', 'schedule');

// データベースへの接続を管理するクラス
class dbConnection {

  // インスタンス
  protected static $db;
  // コンストラクタ
  private function __construct() {

    try {

      // 環境変数からデータベースへの接続情報を取得し
      $url = parse_url(getenv('DATABASE_URL'));
      // データソース
      $dsn = sprintf('pgsql:host=%s;dbname=%s', $url['host'], substr($url['path'], 1));
      // 接続を確立
      self::$db = new PDO($dsn, $url['user'], $url['pass']);
      // エラー時例外を投げるように設定
      self::$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

    }
    catch (PDOException $e) {

      error_log('Connection Error: ' . $e->getMessage());

    }

  }

  // シングルトン。存在しない場合のみインスタンス化
  public static function getConnection() {

    if (!self::$db) {
      new dbConnection();
    }
    return self::$db;

  }

}

// 出題中テーブルにユーザ情報を登録
function registerUser($userId, $questId) {

  $dbh = dbConnection::getConnection();
  $sql = 'insert into '. T_LOGIN_USER .' (userid, questid) values (pgp_sym_encrypt(?, \'' . getenv('DB_ENCRYPT_PASS') . '\'), ?)';
  $sth = $dbh->prepare($sql);
  $sth->execute(array($userId, $questId));

}

// 出題中テーブルよりユーザ情報を削除
function deleteUser($userId) {

  $dbh = dbConnection::getConnection();
  $sql = 'delete from ' . T_LOGIN_USER . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
  $sth = $dbh->prepare($sql);
  $flag = $sth->execute(array($userId));

}

// 全問題id（リスト）を取得
function getAllQuests(){

  $dbh = dbConnection::getConnection();
  $sql = 'select questid from ' . T_QUEST . ' order by 1';
  $sth = $dbh->prepare($sql);
  $sth->execute();

//  $quests = "";
//  while( $row = $sth->fetch(PDO::FETCH_BOTH) ){
//    $quests = $quests . ', \'' . $row['questid'] . '\'';
//  }
  $row = $sth->fetchall(PDO::FETCH_COLUMN);
  $quests = implode(',', $row);

  return $quests;

}

// 出題中テーブルより、出題中の問題IDを取得
function getUserInfo($userId) {

  $dbh = dbConnection::getConnection();
  $sql = 'select userid, questid from ' . T_LOGIN_USER . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
  $sth = $dbh->prepare($sql);
  $sth->execute(array($userId));

  // レコードが存在しなければNULL
  if (!($row = $sth->fetch())) {

    return PDO::PARAM_NULL;

  } else {

    // 出題中の問題を返却する
    return $row['questid'];

  }

}

// 出題履歴を登録
function setQuestHistory($userId, $questId) {

  $dbh = dbConnection::getConnection();
  $sql = 'insert into '. T_Q_HISTORY .' (dttm, userid, questid) values (now() AT TIME ZONE \'Asia/Tokyo\', pgp_sym_encrypt(?, \'' . getenv('DB_ENCRYPT_PASS') . '\'), ?)';
  $sth = $dbh->prepare($sql);
  $sth->execute(array($userId, $questId));

}

// 回答履歴を登録
function setAnswerHistory($userId, $questId, $result) {

  $dbh = dbConnection::getConnection();
  $sql = 'insert into '. T_A_HISTORY .' (dttm, userid, questid, result) values (now() AT TIME ZONE \'Asia/Tokyo\', pgp_sym_encrypt(?, \'' . getenv('DB_ENCRYPT_PASS') . '\'), ?, ?)';
  $sth = $dbh->prepare($sql);
  $sth->execute(array($userId, $questId, $result));

}

// 問題集テーブルより問題文を取得
function getQuestion($questId) {

  $dbh = dbConnection::getConnection();
  $sql = 'select questid, question from '. T_QUEST .' where questid = ?';
  $sth = $dbh->prepare($sql);
  $sth->execute(array($questId));
  $row = $sth->fetch();
  return $row['questid'] . chr(10) . $row['question'];

}

// 問題集テーブルより選択肢を取得
function getChoices($questId) {

  $dbh = dbConnection::getConnection();
  $sql = 'select choice1,choice2,choice3,choice4 from '. T_QUEST .' where questid = ?';
  $sth = $dbh->prepare($sql);
  $sth->execute(array($questId));
  $row = $sth->fetch();
  return $row['choice1'] . chr(10) .$row['choice2'] . chr(10) . $row['choice3'] . chr(10) . $row['choice4'];

}

// 問題集テーブルより回答を取得
function getAnswer($questId) {

  $dbh = dbConnection::getConnection();
  $sql = 'select answer from '. T_QUEST .' where questid = ?';
  $sth = $dbh->prepare($sql);
  $sth->execute(array($questId));
  $row = $sth->fetch();
  return $row['answer'];

}

// 問題集テーブルより解説を取得
function getComment($questId) {

  $dbh = dbConnection::getConnection();
  $sql = 'select comment from '. T_QUEST .' where questid = ?';
  $sth = $dbh->prepare($sql);
  $sth->execute(array($questId));
  $row = $sth->fetch();
  return $row['comment'];

}

// 問題集テーブルより未正解の問題を取得
function getQuestid($userId, $level) {

  $dbh = dbConnection::getConnection();
  $sql = 'select questid from ' . T_QUEST . ' q where not exists (select 1 from ' . T_A_HISTORY . ' a where a. questid = q.questid and result = \'1\' and pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') = ?) and level = ?';
  $sth = $dbh->prepare($sql);
  $sth->execute(array($userId, $level));
  $row = $sth->fetchall(PDO::FETCH_BOTH);
  if(count($row) === 0){

      return PDO::PARAM_NULL;

  }
  // 抽出した配列から１件を取得
  $key = array_rand($row);
  return $row[$key]['questid'];

}

// 問題集テーブルより未正解の問題を取得
// 回答履歴（answer_history）を定期的にパージするため、正解済の問題の識別方法を変更
function getQuestidEx($userId, $level) {

  $dbh = dbConnection::getConnection();

  $sql = 'select rm_quests from ' . T_FRIENDS . ' where pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') = ?';
  $sth = $dbh->prepare($sql);
  $sth->execute(array($userId));
  $row = $sth->fetch();
  $list = $row['rm_quests'];

  $arrays = explode(',', $list);
  if($level === 1){
      $result = array_filter($arrays, "match_fe");
  }
  else{
      $result = array_filter($arrays, "match_ap");
  }

  // 配列が空の場合
  if(count($result) === 0){
      return PDO::PARAM_NULL;
  }
  // 抽出した配列から１件を取得
  $key = array_rand($result);
  return $result[$key];

}
function match_fe($value) {
    if(strpos($value, 'FE-') === 0){
        return true;
    }
    return false;
}
function match_ap($value) {
    if(strpos($value, 'AP-') === 0){
        return true;
    }
    return false;
}

// 友だちテーブルにユーザ情報を登録（FollowEvent発生時）
function registerFrends($userId, $name, $pictureUrl) {

  $remains = getAllQuests();

  $dbh = dbConnection::getConnection();
  $sql = 'insert into '. T_FRIENDS .' (userid, name, pictureUrl, level, rm_quests) values (pgp_sym_encrypt(?, \'' . getenv('DB_ENCRYPT_PASS') . '\'), ?, ?, 1, ?)';
  $sth = $dbh->prepare($sql);
  $sth->execute(array($userId, $name, $pictureUrl, $remains));

}

// 友だちテーブルよりユーザ情報を削除（UnfollowEvent発生時）
function deleteFrends($userId) {

  $dbh = dbConnection::getConnection();
  $sql = 'delete from ' . T_FRIENDS . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
  $sth = $dbh->prepare($sql);
  $flag = $sth->execute(array($userId));

}

// 友だちテーブルを更新
function updateFrends($userId, $name, $pictureUrl, $except) {

  $dbh = dbConnection::getConnection();
  $sql = 'select replace(rm_quests, ?, \'\') as remains from ' . T_FRIENDS . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
  $sth = $dbh->prepare($sql);
  $sth->execute(array($except, $userId));
  $row = $sth->fetch();
  $remains = $row['remains'];

  $sql = 'update ' . T_FRIENDS . ' set name = ?, pictureUrl = ?, rm_quests = ?  where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
  $sth = $dbh->prepare($sql);
  $flag = $sth->execute(array($name, $pictureUrl, $remains, $userId));

}

// 友だちテーブルよりLEVEL（1:FE／2:AP）を取得
function getLevel($userId) {

  $dbh = dbConnection::getConnection();
  $sql = 'select level from '. T_FRIENDS .' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
  $sth = $dbh->prepare($sql);
  $sth->execute(array($userId));
  $row = $sth->fetch();
  return $row['level'];

}

// 友だちテーブルのユーザ情報（LEVEL）を変更
function setLevel($userId, $level) {

  $dbh = dbConnection::getConnection();
  $sql = 'update '. T_FRIENDS .' set level = ? where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
  $sth = $dbh->prepare($sql);
  $sth->execute(array($level, $userId));

}

// 友だちテーブルの社員情報を変更
function setEmpInfo($userId, $emp_no, $emp_name) {

  $dbh = dbConnection::getConnection();
  $sql = 'update '. T_FRIENDS .' set employee_no = ?, employee_name = ? where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
  $sth = $dbh->prepare($sql);
  $sth->execute(array($emp_no, $emp_name, $userId));

}

// スケジュールテーブルより配信フラグを取得
function getDeliveryFlg($time_h) {

  $dbh = dbConnection::getConnection();
  $sql = 'select delivery_flg from '. T_SCHEDULE .' where time_h = ?';
  $sth = $dbh->prepare($sql);
  $sth->execute(array($time_h));
  $row = $sth->fetch();
  return $row['delivery_flg'];

}

// スケジュールテーブルの配信フラグを「配信済」に変更
function setDeliveryFlg($time_h) {

  $dbh = dbConnection::getConnection();
  $sql = 'update '. T_SCHEDULE .' set delivery_flg = \'1\' where time_h = ?';
  $sth = $dbh->prepare($sql);
  $sth->execute(array($time_h));

}

// スケジュールテーブルの全配信フラグを「未配信」に変更
function clrDeliveryFlg() {

  $dbh = dbConnection::getConnection();
  $sql = 'update '. T_SCHEDULE .' set delivery_flg = \'0\'';
  $sth = $dbh->prepare($sql);
  $sth->execute();

}

// 暗号化したユーザidのHEX文字列を取得
function getEnccode($userId) {

  $dbh = dbConnection::getConnection();
  $sql = 'select encode(pgp_sym_encrypt(?, \'' . getenv('DB_ENCRYPT_PASS') . '\'), \'hex\') as enc';
  $sth = $dbh->prepare($sql);
  $sth->execute(array($userId));
  $row = $sth->fetch();
  return $row['enc'];

}

?>