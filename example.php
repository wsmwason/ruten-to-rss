<?php

include("RutenSearch.php");

// 直接把搜尋結果從 GET 傳入
$result_url = isset($_GET['result_url']) ? $_GET['result_url'] : '';

// 讀取搜尋結果網址的資料
$RutenSearch = new RutenSearch($result_url);
$RutenSearch->asXml();