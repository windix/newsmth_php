<?php
header("Content-Type:text/html;charset=UTF-8");

$post = file_get_contents("post/RealEstate-1139883");
echo "<pre>";
// echo $post;

//preg_match("|发信人: ([\w\d]+) \((.*)\), 信区: (\w+)<br />标  题: (.*)<br />|U", $post, $matches);

$post = preg_replace("|发信人: ([\w\d]+) \((.*)\), 信区: (\w+)<br />标  题: (.*)<br />|U", "发信人: <a href='http://www.newsmth.net/bbsqry.php?userid=$1'>$1 ($2)</a>, 信区: <a href='http://www.newsmth.net/bbsdoc.php?board=$3'>$3</a><br />标  题: <a href='$url'>$4</a><br />", $post);

?>