<?php

require "curl.php";

//$url = "http://www.newsmth.net/bbsbfind.php?q=1&board=FamilyLife&title=%BF%B4%C7%F2%D3%EBgf%B2%BB%BF%C9%BC%E6%B5%C3%A3%BF%D5%E6%D0%C4%C7%F3%BD%CC&title2=&title3=&userid=&dt=7";

$url = urldecode('http%3A%2F%2Fwww.newsmth.net%2Fbbsbfind.php%3Fq%3D1%26board%3D%26title%3D%27%27%26title2%3D%26title3%3D%26userid%3D%26dt%3D7');

echo $url;

die;
$content = curl_get($url);

preg_match("|<a href=\"(bbscon\.php\?bid=\d+&id=\d+)\"|m", $content, $m);

echo "<pre>";
print_r($m);


?>
