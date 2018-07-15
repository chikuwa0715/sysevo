<?php
    include_once "database.php";

//echo('<pre>');
//var_dump(getAllQuests());
//echo('</pre>');

//updateFrends('Ua42fd2c4c821377ac6f4e01ba5393ffd','たけし','http://dl.profile.line-cdn.net/0hWL8dWEfgCGMFHSf6cqd3NDlYBg5yMw4rfSkUDHBOXlQoLUllPC9HUCNIUVcuf05naixEBSMaAVF6','AP-H26-AT-02');

echo('<pre>');
var_dump(getQuestidEx('Ue9b69e8f47eccb5d1e46b22a19abbafb',1));
echo('</pre>');

//$dbh = dbConnection::getConnection();
//$sql = 'update ' . T_FRIENDS . ' set rm_quests = ?  where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
//$sth = $dbh->prepare($sql);
//$flag = $sth->execute(array(getAllQuests(), 'Ua42fd2c4c821377ac6f4e01ba5393ffd'));



?>
