<?php
require "lib/douconv.php";
require "curl.php";

putenv("TZ=Australia/Melbourne");
set_time_limit(0);

// echo "<pre>";
echo date("Y-m-d H:i:s\n");

$POST_PATH = "./post/";
$ATT_PATH = "./att/";
$LIST_FILE = "list.txt";

$board_data = unserialize(file_get_contents("board_data.txt"));

$rss_url = "http://www.newsmth.net/rssi.php?h=1";
$content = curl_get($rss_url);

preg_match_all('|<guid>(http://www.newsmth.net/bbstcon.php\?board=(\w+)&amp;gid=(\d+))</guid>|', $content, $matches);

$count = count($matches[0]);
$urls_bbstcon = $matches[1];
$boards = $matches[2];
$ids = $matches[3];

$list_fp = fopen($LIST_FILE, "a");

for($i=0; $i<$count; $i++) {
    $board = $boards[$i];
    $id = $ids[$i];

    // check if exist
    $post_file = $POST_PATH.$board."-".$id;
    if (file_exists($post_file)) {
        continue;
    }

    echo "Update $board - $id...\n";

/*    
    // Get board id
    $content_bbstcon = curl_get(str_replace("&amp;", "&", $urls_bbstcon[$i]));
    
    if (preg_match("|tconWriter\('.*',(\d+),|m", $content_bbstcon, $m)) {
        $board_id = $m[1];
 */        
    $board_id = $board_data[$board];
    if ($board_id) {
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

            // output post
            $fp = fopen($post_file, "w");
            fwrite($fp, $post);
            fclose($fp);

            // get attachments (if have)
            $j = 1;
            if (preg_match_all("|attach\('.*', \d+, (\d+)\);|mU", $content, $m) > 0) {
                foreach($m[1] as $att_id) {
                    echo "Fetch attachment $board - $id - $j...\n";
                    
                    $att_url = "http://www.newsmth.net/att.php?s.$board_id.$id.$att_id.jpg";
                    
                    $content = curl_get($att_url);
                    $att_file = $ATT_PATH.$board."-".$id."-".$j.".jpg";

                    // output att
                    $fp = fopen($att_file, "w");
                    fwrite($fp, $content);
                    fclose($fp);
                    
                    $j++;
                }
            }

            // update list file
            // format: timestamp|postname|attach_count

            $line = time()."|".$board."-".$id."|".($j-1)."\n";
            fwrite($list_fp, $line);
        } else {
            echo "FAILED to load content!\n";
        }
    } else {
        echo "FAILED to get board_id for board: $board\n";
    }     
/*    
    } else {
        echo "FAILED!\n";
    }
 */
} // for

fclose($list_fp);

echo "\n";

?>
