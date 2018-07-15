<?php
include_once "database.php";

//スケジュールテーブルの全配信フラグを「未配信」に設定
//curl https://sysevotest.herokuapp.com/clear_delivery_flg.php
clrDeliveryFlg();
error_log("Clear Delivery Flg!");

?>
