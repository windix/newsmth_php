<?php
require "star.php";
$arr = load_starlist();

foreach ($arr as $id) {
  // star is zero based
  echo ($id + 1)."\n";
}
?>
