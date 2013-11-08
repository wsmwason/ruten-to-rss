<?php

$url = isset($_GET['url']) ? $_GET['url'] : '';

$url_info = parse_url($url);

$domains = array(
  'a.rimg.com.tw',
  'b.rimg.com.tw',
  'c.rimg.com.tw',
  'd.rimg.com.tw',
  'e.rimg.com.tw',
  'f.rimg.com.tw',
);

if(!isset($url_info['host'])) die("Can't access!");
if(!in_array($url_info['host'], $domains)) die("Can't access!");

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1; rv:24.0) Gecko/20100101 Firefox/24.0');
curl_setopt($ch, CURLOPT_REFERER, 'http://www.ruten.com.tw');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$result = curl_exec($ch);
curl_close($ch);

header('Content-Type: image/jpeg');
echo $result;