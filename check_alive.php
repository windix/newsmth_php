<?php
require "curl.php";

$url = urldecode($_GET['url']);
$search_url = urldecode($_GET['search_url']);
$divid = $_GET['divid'];

$html = "";
if (check_alive($url)) {
    $html = "<a href=\'$url\'>Link</a>";
} else {
    $r = check_search_result($search_url);

    if ($r) {
        $html = "<a href=\'$r\'>Bookmark Link</a>";
    } else {
        $html = "-";
    }
}

echo "document.getElementById(divid).innerHTML = '$html';";

function check_alive($url) {
    $content = curl_get($url);
    return (strpos($content, '<table class="error">') === false);
}

function check_search_result($url) {
    $content = curl_get($url);
    if (preg_match("|<a href=\"(bbscon\.php\?bid=\d+&id=\d+)\"|m", $content, $m)) {
        return "http://www.newsmth.net/".$m[1];
    } else {
        return null;
    }
}

?>
