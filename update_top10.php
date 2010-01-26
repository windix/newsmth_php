<?php
require "lib/douconv.php";
require "curl.php";

putenv("TZ=Australia/Melbourne");
set_time_limit(0);

echo date("Y-m-d H:i:s\n");

$POST_PATH = "./post/";
$ATT_PATH = "./att/";
$LIST_FILE = "list.txt";

$board_data = unserialize(file_get_contents("board_data.txt"));

$count = parse_rss($urls_bbstcon, $boards, $ids);

$list_fp = fopen($LIST_FILE, "a");

for($i=0; $i<$count; $i++) {
  $board = $boards[$i];
  $id = $ids[$i];

  // check if exist
  $post_file = $POST_PATH.$board."-".$id;
  if (file_exists($post_file)) {
    continue;
  }

  if ($board_id = get_board_id($board, $urls_bbstcon[$i])) {
    echo "Update {$board}[{$board_id}] - $id...\n";

    if ($post = get_post($board, $board_id, $id, $att_count)) {
      // output post
      $fp = fopen($post_file, "w");
      fwrite($fp, $post);
      fclose($fp);


      // update list file
      // format: timestamp|postname|attach_count

      $line = time()."|".$board."-".$id."|".$att_count."\n";
      fwrite($list_fp, $line);
    } else {
      echo "FAILED to get post!\n";
    }
  } else {
    echo "FAILED to get board_id for board: $board\n";
  }     
} // for

fclose($list_fp);

echo "\n";

////////////////////////////////////////////////////

function parse_rss(&$urls_bbstcon, &$boards, &$ids) {
  $rss_url = "http://www.newsmth.net/rssi.php?h=1";
  $content = curl_get($rss_url);

  $count = preg_match_all('|<guid>(http://www.newsmth.net/bbstcon.php\?board=(\w+)&amp;gid=(\d+))</guid>|', $content, $matches);

  $urls_bbstcon = $matches[1];
  $boards = $matches[2];
  $ids = $matches[3];
  
  return (int)$count;
}

function get_board_id($board, $bbstcon_url) {
  global $board_data;
  
  $board_id = $board_data[$board];
  
  if (!$board_id) {
    echo "*** board_data.txt need to update!\n";

    $content_bbstcon = curl_get(str_replace("&amp;", "&", $bbstcon_url));
    
    if (preg_match("|tconWriter\('.*',(\d+),|m", $content_bbstcon, $m)) {
      $board_id = $m[1];
      echo "{$board}[{$board_id}] fetched\n";
    
    } else {
      return null;
    }
  }

  return $board_id;
}

function get_post($board, $board_id, $id, &$att_count) {
  $post = null;
  
  $url = "http://www.newsmth.net/bbscon.php?bid=$board_id&id=$id";

  // Get content
  $content = curl_get($url);

  // get post content
  if (preg_match("|^prints\('(.*)\\\\r\[m\\\\n|m", $content, $m)) {
    $post = $m[1];

    // clean the post content
    $post = str_replace("\\n", "<br />", $post);
    $post = stripslashes($post);
    $post = preg_replace("|r\[[0-9;]*m|", "", $post);

    // iconv
    $post = iconv("GBK", "UTF-8//IGNORE", $post);
  
    // download attachements (if any)
    $att_count = download_attachments($content, $board, $board_id, $id);
  }
  
  return $post;
}

function download_attachments($content, $board, $board_id, $id) {
  global $ATT_PATH;
  
  if ($count = preg_match_all("|attach\('([^']+)', \d+, (\d+)\);|m", $content, $m)) {
    for($i = 1; $i <= $count; $i++) {
      $pathinfo = pathinfo($m[1][$i-1]);
      $ext_name = strtolower($pathinfo['extension']);
      $att_id = $m[2][$i-1];

      $filename = "{$board}-{$id}-{$i}.{$ext_name}";

      echo "Fetch attachment $filename...\n";
      $att_url = "http://www.newsmth.net/att.php?s.$board_id.$id.$att_id";
      $content = curl_get($att_url);
    
      // output att
      $fp = fopen($ATT_PATH.$filename, "w");
      fwrite($fp, $content);
      fclose($fp);
    }
  }

  return (int)$count;
}

?>
