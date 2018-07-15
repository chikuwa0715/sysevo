<!DOCTYPE html>
<html>
  <head prefix="og: http://ogp.me/ns#">
    <meta charset="utf-8" />
    <title>成績照会</title>
    <meta name="description" content="情報処理一問一答に参加している友だちの、成績一覧を表示します。" />
    <meta property="og:title" content="成績照会" />
    <meta property="og:type" content="article" />
    <meta property="og:url" content="http://sysevotest.herokuapp.com/" />
    <meta property="og:image" content="http://sysevotest.herokuapp.com/header_logo2.png" />
    <meta property="og:image:type" content="image/png" />
    <meta property="og:image:alt" content="システム・エボリューション株式会社" />
    <meta property="og:description" content="情報処理一問一答に参加している友だちの、成績一覧を表示します。" />
    <style>
    td.label{font-weight:bold; color:#26CE61; background-color:#FAFAFA;}
    </style>
  </head>
<body>
  <img alt="システム・エボリューション株式会社" src="http://sysevotest.herokuapp.com/header_logo2.png" />
  <img alt="GitHubに移植しました♪" src="http://sysevotest.herokuapp.com/sysevo.png" />
<?php
include_once "database.php";

//成績照会ページ
//curl https://sysevotest.herokuapp.com/query_results.php

    $dbh = dbConnection::getConnection();

echo "<h3>友だち一覧／成績一覧</h3>" . PHP_EOL ;
echo "<table border=1>" . PHP_EOL ;
echo "<tr><td class=\"label\">画像</td><td class=\"label\">表示名</td><td class=\"label\">level</td><td class=\"label\">社員番号</td><td class=\"label\">社員名</td><td class=\"label\"></td><td class=\"label\">出題数</td><td class=\"label\">回答数</td><td class=\"label\">正解数</td><td class=\"label\">回答率</td><td class=\"label\">正解率</td><td class=\"label\">最終出題日時</td><td class=\"label\">最終回答日時</td></tr>" . PHP_EOL ;

    // 成績一覧を抽出
    $sql =        'select fd.pictureUrl,';
    $sql = $sql . '       fd.name,';
    $sql = $sql . '       (case fd.level when 1 then \'FE\' when 2 then \'AP\' END) as level,';
    $sql = $sql . '       fd.employee_no,';
    $sql = $sql . '       fd.employee_name,';
    $sql = $sql . '       COALESCE(qh.req,0) as req,';
    $sql = $sql . '       COALESCE(ah.res,0) as res,';
    $sql = $sql . '       COALESCE(ah.success,0) as success,';
    $sql = $sql . '       qh2.last_que,';
    $sql = $sql . '       ah2.last_ans';
    $sql = $sql . '  from (select pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') as userid, name, pictureUrl, level, employee_no, employee_name from friends) as fd';
    $sql = $sql . '       left outer join (select pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') as userid, count(questid) as req from question_history group by pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')) as qh using(userid)';
    $sql = $sql . '       left outer join (select pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') as userid, count(questid) as res, sum(case result when \'1\' then 1 else 0 end) as success from answer_history group by pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')) as ah using(userid)';
    $sql = $sql . '       left outer join (select pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') as userid, to_char(max(dttm), \'YYYY/MM/DD HH24:MI:SS\') as last_que from question_history group by pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')) as qh2 using(userid)';
    $sql = $sql . '       left outer join (select pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') as userid, to_char(max(dttm), \'YYYY/MM/DD HH24:MI:SS\') as last_ans from answer_history group by pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')) as ah2 using(userid)';
    $sql = $sql . ' order by name';
    $sth = $dbh->prepare($sql);
    $sth->execute();

    while( $row = $sth->fetch(PDO::FETCH_BOTH) ){

echo "<tr>" ;
echo "<td><img src=\"" . $row["pictureurl"] . "\" width=50 height=50 /></td>" ;
echo "<td>" . $row["name"] . "</td>" ;
echo "<td>" . $row["level"] . "</td>" ;
echo "<td>" . $row["employee_no"] . "</td>" ;
echo "<td>" . $row["employee_name"] . "</td>" ;
echo "<td></td>" ; 
echo "<td>" . $row["req"] . "</td>" ;
echo "<td>" . $row["res"] . "</td>" ; 
echo "<td>" . $row["success"] . "</td>" ; 
if($row["req"] == 0) {
    echo "<td>---%</td>" ; 
} else {
    echo "<td>" . round($row["res"]/$row["req"]*100, 1) . "%</td>" ; 
}
if($row["res"] == 0) {
    echo "<td>---%</td>" ; 
} else {
    echo "<td>" . round($row["success"]/$row["res"]*100, 1) . "%</td>" ; 
}
echo "<td>" . $row["last_que"] . "</td>" ; 
echo "<td>" . $row["last_ans"] . "</td>" ; 
echo "</tr>" . PHP_EOL ;

    }

echo "</table>" . PHP_EOL ;
echo "<br />" . PHP_EOL ;
echo "<br />" . PHP_EOL ;

echo "<h3>収録済み過去問</h3>" . PHP_EOL ;
echo "<table border=1>" . PHP_EOL ;
echo "<tr><td class=\"label\">level</td><td class=\"label\">年度</td><td class=\"label\">開期</td><td class=\"label\">問題数</td></tr>" . PHP_EOL ;

    // 収録済み過去問を抽出
    $sql =        'select level, gee, season, count(level) as quests';
    $sql = $sql . '  from (select case level when 1 then \'FE\' when 2 then \'AP\' END as level,';
    $sql = $sql . '               \'平成\' || substring(questid from 5 for 2) || \'年\' as gee,';
    $sql = $sql . '               case substring(questid from 8 for 2) when \'SP\' then \'春期\' when \'AT\' then \'秋期\' end as season';
    $sql = $sql . '          from questions) qs';
    $sql = $sql . ' group by level, gee, season';
    $sql = $sql . ' order by level desc, gee asc, season asc';
    $sth = $dbh->prepare($sql);
    $sth->execute();

    while( $row = $sth->fetch(PDO::FETCH_BOTH) ){

echo "<tr>" ;
echo "<td>" . $row["level"] . "</td>" ;
echo "<td>" . $row["gee"] . "</td>" ;
echo "<td>" . $row["season"] . "</td>" ; 
echo "<td>" . $row["quests"] . "</td>" ; 
echo "</tr>" . PHP_EOL ;

    }

echo "</table>" . PHP_EOL ;

?>
</body>
</html>
