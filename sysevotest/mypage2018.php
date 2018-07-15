<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>マイページ</title>
<style>
td.label{
  font-weight:bold; color:#26CE61; background-color:#FAFAFA;}
.link_button {
  text-decoration:none; background:skyblue; font-weight:bold; color:white;
  padding:5px 10px; border-radius:20px;}
</style>
<script type="text/javascript">
<!--
function checkSubmit() {
    if(document.form1.emp_no.value == ""){
        window.alert('社員番号を入力してください'); 
        return false;
    }
    if(document.form1.emp_no.value.match(/[^0-9]+/) || document.form1.emp_no.value.length != 4){
        window.alert('社員番号には数字４桁の値を入力してください'); 
        return false;
    }
    if(document.form1.emp_name.value == ""){
        window.alert('氏名を入力してください'); 
        return false;
    }
    return confirm("登録しても良いですか？");
}
// -->
</script>
</head>
<body>
<?php
    include_once "database.php";
    $levels = array("", "FE", "AP");

    $id = $_GET['id'];

    $dbh = dbConnection::getConnection();

    // 登録情報を抽出
    $sql =        'select pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') as userid,';
    $sql = $sql . '       pictureUrl,';
    $sql = $sql . '       name,';
    $sql = $sql . '       employee_no,';
    $sql = $sql . '       employee_name,';
    $sql = $sql . '       rm_quests,';
    $sql = $sql . '       level,';
    $sql = $sql . '       gift_url';
    $sql = $sql . '  from '. T_FRIENDS;
    $sql = $sql . ' where pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') = pgp_sym_decrypt(decode(:id, \'hex\'), \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sth = $dbh->prepare($sql);
//    $sth->execute(array(':id' => $id));
    $sth->bindValue(':id', $id, PDO::PARAM_STR);
    $sth->execute();
    $row = $sth->fetch(PDO::FETCH_BOTH);

    $userid = $row['userid'];
    $level = $row['level'];
    $remain = $row['rm_quests'];

    echo "<h3>登録情報</h3>" . PHP_EOL ;
    echo "<form name=\"form1\" method=\"post\" action=\"regemp.php\" onSubmit=\"return checkSubmit();\">" . PHP_EOL ;
    echo "<table border=1>" . PHP_EOL ;
    echo "<tr><td class=\"label\">ユーザid</td><td><img src='" . $row['pictureurl'] . "' width=50 height=50 /></td><td>" . $userid . "</td></tr>" . PHP_EOL ;
    echo "<tr><td class=\"label\">表示名</td><td colspan=2>" . $row['name'] . "</td></tr>" . PHP_EOL ;
    echo "<tr><td class=\"label\">Level</td><td colspan=2>" . $levels[$level] . "</td></tr>" . PHP_EOL ;
    echo "<tr><td class=\"label\">社員番号</td><td colspan=2><input type=text name=\"emp_no\"   value=\"" . $row['employee_no'] .   "\" placeholder=\"社員番号4桁で入力\" pattern=\"\d{4}\" maxlength=\"4\" title=\"数字４桁\"></td></tr>" . PHP_EOL ;
    echo "<tr><td class=\"label\">社員名</td>  <td colspan=2><input type=text name=\"emp_name\" value=\"" . $row['employee_name'] . "\" placeholder=\"名前を入力\"></td></tr>" . PHP_EOL ;
    echo "</table>" . PHP_EOL ;
    echo "<button type='submit' name='action' value='send'>更　新</button>" . PHP_EOL ;
    echo "<input type=hidden name=\"userid\" value=\"" . $userid . "\" />" . PHP_EOL ;
    echo "</form>" . PHP_EOL ;

    echo "<br />" . PHP_EOL ;
    if(is_null($row['gift_url'])) {
        echo "<a class=\"link_button\" href='#'>ギフト準備中...</a>" . PHP_EOL ;
    } else {
        echo "<a class=\"link_button\" href='" . $row['gift_url'] . "'>ポチッとギフト</a>" . PHP_EOL ;
    }
    echo "<br />" . PHP_EOL ;
    echo "<br />" . PHP_EOL ;


    // 成績一覧（当月）を抽出
//    $sql =        'select gee,';
//    $sql = $sql . '       season,';
//    $sql = $sql . '       quests,';
//    $sql = $sql . '       req,';
//    $sql = $sql . '       res,';
//    $sql = $sql . '       success';
//    $sql = $sql . '  from';
//    $sql = $sql . '(select \'平成\' || substring(questid from 5 for 2) || \'年\' as gee,';
//    $sql = $sql . '         case substring(questid from 8 for 2) when \'SP\' then \'春期\' when \'AT\' then \'秋期\' end as season,';
//    $sql = $sql . '         count(questid) as quests';
//    $sql = $sql . '    from questions';
//    $sql = $sql . '   where level = :levelnum';
//    $sql = $sql . '   group by \'平成\' || substring(questid from 5 for 2) || \'年\',';
//    $sql = $sql . '            case substring(questid from 8 for 2) when \'SP\' then \'春期\' when \'AT\' then \'秋期\' end) as qs';
//    $sql = $sql . ' left outer join';
//    $sql = $sql . '(select \'平成\' || substring(questid from 5 for 2) || \'年\' as gee,';
//    $sql = $sql . '        case substring(questid from 8 for 2) when \'SP\' then \'春期\' when \'AT\' then \'秋期\' end as season,';
//    $sql = $sql . '        count(questid) as req';
//    $sql = $sql . '   from question_history';
//    $sql = $sql . '  where pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') = :userid';
//    $sql = $sql . '    and substring(questid from 1 for 2) = :levelnm';
//    $sql = $sql . '  group by \'平成\' || substring(questid from 5 for 2) || \'年\',';
//    $sql = $sql . '           case substring(questid from 8 for 2) when \'SP\' then \'春期\' when \'AT\' then \'秋期\' end) as qh using(gee, season)';
//    $sql = $sql . ' left outer join';
//    $sql = $sql . '(select \'平成\' || substring(questid from 5 for 2) || \'年\' as gee,';
//    $sql = $sql . '        case substring(questid from 8 for 2) when \'SP\' then \'春期\' when \'AT\' then \'秋期\' end as season,';
//    $sql = $sql . '        count(questid) as res,';
//    $sql = $sql . '        sum(case result when \'1\' then 1 else 0 end) as success';
//    $sql = $sql . '   from answer_history';
//    $sql = $sql . '  where pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') = :userid';
//    $sql = $sql . '    and substring(questid from 1 for 2) = :levelnm';
//    $sql = $sql . '  group by \'平成\' || substring(questid from 5 for 2) || \'年\',';
//    $sql = $sql . '           case substring(questid from 8 for 2) when \'SP\' then \'春期\' when \'AT\' then \'秋期\' end) as ah using(gee, season)';
//    $sql = $sql . ' order by 1, 2';
//    $sth = $dbh->prepare($sql);
//    $sth->execute(array(':levelnum' => $level, ':levelnm' => $levels[$level], ':userid' => $userid));
//
//    echo "<h3>個人成績（当月）</h3>" . PHP_EOL ;
//    echo "<table border=1>" . PHP_EOL ;
//    echo "<tr><td class=\"label\">年度</td><td class=\"label\">開期</td><td class=\"label\">収録数</td><td class=\"label\">出題数</td><td class=\"label\">回答数</td><td class=\"label\">正解数</td><td class=\"label\">制覇率</td><td class=\"label\">回答率</td><td class=\"label\">正解率</td></tr>" . PHP_EOL ;
//    while( $row = $sth->fetch(PDO::FETCH_BOTH) ){
//
//        echo "<tr>" ;
//        echo "<td>" . $row['gee'] . "</td>" ;
//        echo "<td>" . $row['season'] . "</td>" ;
//        echo "<td>" . $row['quests'] . "</td>" ;
//        echo "<td>" . $row['req'] . "</td>" ;
//        echo "<td>" . $row['res'] . "</td>" ; 
//        echo "<td>" . $row['success'] . "</td>" ; 
//        if($row['quests'] == 0) {
//            echo "<td>---%</td>" ; 
//        } else {
//            echo "<td>" . round($row['success']/$row['quests']*100, 1) . "%</td>" ; 
//        }
//        if($row['req'] == 0) {
//            echo "<td>---%</td>" ; 
//        } else {
//            echo "<td>" . round($row['res']/$row['req']*100, 1) . "%</td>" ; 
//        }
//        if($row['res'] == 0) {
//            echo "<td>---%</td>" ; 
//        } else {
//            echo "<td>" . round($row['success']/$row['res']*100, 1) . "%</td>" ; 
//        }
//        echo "</tr>" . PHP_EOL ;
//
//    }
    $sql =        'select COALESCE(qh.req,0) as req,';
    $sql = $sql . '       COALESCE(ah.res,0) as res,';
    $sql = $sql . '       COALESCE(ah.success,0) as success';
    $sql = $sql . '  from (select pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') as userid';
    $sql = $sql . '          from friends';
    $sql = $sql . '         where pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') = pgp_sym_decrypt(decode(:id, \'hex\'), \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sql = $sql . '       ) as fd';
    $sql = $sql . '       left outer join (select pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') as userid, count(questid) as req from question_history group by pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')) as qh using(userid)';
    $sql = $sql . '       left outer join (select pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') as userid, count(questid) as res, sum(case result when \'1\' then 1 else 0 end) as success from answer_history group by pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')) as ah using(userid)';

    $sth = $dbh->prepare($sql);
    $sth->bindValue(':id', $id, PDO::PARAM_STR);
    $sth->execute();
    $row = $sth->fetch(PDO::FETCH_BOTH);

    echo "<h3>個人成績</h3>" . PHP_EOL ;
    echo "<table border=1>" . PHP_EOL ;
    echo "<tr><td class=\"label\">年月</td><td class=\"label\">出題数</td><td class=\"label\">回答数</td><td class=\"label\">正解数</td><td class=\"label\">回答率</td><td class=\"label\">正解率</td></tr>" . PHP_EOL ;

    echo "<tr>" ;
    echo "<td>当月</td>" ;
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
    echo "</tr>" . PHP_EOL ;

    // 個人成績（アーカイブ）を抽出
    $sql =        'select yyyymm,';
    $sql = $sql . '       request,';
    $sql = $sql . '       response,';
    $sql = $sql . '       correct,';
    $sql = $sql . '       response_rate,';
    $sql = $sql . '       correct_rate';
    $sql = $sql . '  from personal_record';
    $sql = $sql . ' where pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\') = pgp_sym_decrypt(decode(:id, \'hex\'), \'' . getenv('DB_ENCRYPT_PASS') . '\')';
    $sql = $sql . ' order by yyyymm desc';

    $sth = $dbh->prepare($sql);
    $sth->bindValue(':id', $id, PDO::PARAM_STR);
    $sth->execute();
    while( $row = $sth->fetch(PDO::FETCH_BOTH) ){

        echo "<tr>" ;
        echo "<td>" . $row['yyyymm'] . "</td>" ;
        echo "<td>" . $row['request'] . "</td>" ;
        echo "<td>" . $row['response'] . "</td>" ;
        echo "<td>" . $row['correct'] . "</td>" ;
        echo "<td>" . round($row['response_rate'] * 100, 2) . "%</td>" ;
        echo "<td>" . round($row['correct_rate'] * 100, 2) . "%</td>" ; 
        echo "</tr>" . PHP_EOL ;

    }

    echo "</table>" . PHP_EOL ;
    echo "<br />" . PHP_EOL ;
    echo "<br />" . PHP_EOL ;


    // 収録済み過去問を抽出
    $sql =        'select substring(questid from 1 for 9) as piece , level, gee, season, count(level) as quests';
    $sql = $sql . '  from (select questid,case level when 1 then \'FE\' when 2 then \'AP\' END as level,';
    $sql = $sql . '               \'平成\' || substring(questid from 5 for 2) || \'年\' as gee,';
    $sql = $sql . '               case substring(questid from 8 for 2) when \'SP\' then \'春期\' when \'AT\' then \'秋期\' end as season';
    $sql = $sql . '          from questions) qs';
    $sql = $sql . ' group by substring(questid from 1 for 9), level, gee, season';
    $sql = $sql . ' order by level desc, gee asc, season asc';
    $sth = $dbh->prepare($sql);
    $sth->execute();

    echo "<h3>制覇率</h3>" . PHP_EOL ;
    echo "<table border=1>" . PHP_EOL ;
    echo "<tr><td class=\"label\">level</td><td class=\"label\">年度</td><td class=\"label\">開期</td><td class=\"label\">問題数</td><td class=\"label\">残り</td><td class=\"label\">制覇率</td></tr>" . PHP_EOL ;

    while( $row = $sth->fetch(PDO::FETCH_BOTH) ){

        $last = substr_count($remain, $row["piece"]);
        echo "<tr>" ;
        echo "<td>" . $row["level"] . "</td>" ;
        echo "<td>" . $row["gee"] . "</td>" ;
        echo "<td>" . $row["season"] . "</td>" ; 
        echo "<td>" . $row["quests"] . "</td>" ; 
        echo "<td>" . $last . "</td>" ; 
        echo "<td>" . round(($row["quests"] - $last)/$row["quests"] * 100, 2) . "%</td>" ; 
        echo "</tr>" . PHP_EOL ;

    }

    echo "</table>" . PHP_EOL ;

?>
</body>
</html>
