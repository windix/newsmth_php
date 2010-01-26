<?php
/*
  Entry: http://www.newsmth.net/bbsfav.php?x

  Directory: (link to http://www.newsmth.net/bbsfav.php?select=1&x)
  o.f(1,'系统　　　 水木社区系统版面 ',0,'NewSMTH.net');

  Board:
  o.o(false,1,104,10468417,'[出国]','AdvancedEdu','飞跃重洋','TimeBlue madonion AngelWang luluto',33766,103,53);
  
  board_id: 104
  board_name: AdvancedEdu
  board_name_cn: 飞跃重洋
 */

require "curl.php";

$entry_page = "http://www.newsmth.net/bbsfav.php?x";
$content = curl_get($entry_page);

$board_list = parse_page($content);

$board_data = array();
foreach($board_list as $board) {
  $board_data[$board[1]] = $board[0];
}

file_put_contents("board_data.txt", serialize($board_data));
echo "Update board_data finished\n";

function remove_quotes($s) {
  if ($s[0] == "'") $s = substr($s, 1, -1);
  return $s;
}

function parse_page($content) {
  $board_list = array();

  if (preg_match_all('/o\.f\((\d+)/', $content, $matches)) {
    foreach($matches[1] as $dir_id) {
      $sub_page = "http://www.newsmth.net/bbsfav.php?select=$dir_id&x";
      $content = curl_get($sub_page);

      echo "dir_id = $dir_id\n";
      $board_list = array_merge($board_list, parse_page($content));
    }
  }

  if (preg_match_all('/o\.o\(([^)]+)\)/', $content, $matches)) {
    foreach($matches[1] as $s) {
      $l = explode(',', $s);

      // board_id, board_name, board_name_cn
      $board_list[] = array($l[2], remove_quotes($l[5]), remove_quotes($l[6]));
    }
  }

  return $board_list;
}

?>
