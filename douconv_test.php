<?php
require "lib/douconv.php";

header("content-type:text/html;charset=utf-8");

$s = file_get_contents("gb.txt");
echo iconv("gbk", "utf-8", $s);

?>
