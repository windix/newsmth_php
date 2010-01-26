<?php
require("lib/douconv.php");
require("star.php");

$LIST_FILE = "list.txt";

putenv("TZ=Australia/Melbourne");

header("Content-Type:text/html;charset=UTF-8");
?>
<html>
<head>
<title>NewSMTH Top10 Archives</title>
<link rel="shortcut icon" href="favicon.ico" />
<script language="javascript" src="download_url.js"></script>
<script language="javascript">
function toggle_star(id, imgp) {
    downloadUrl('update_star.php?id=' + id, function(data, responseCode) {});
    
    var is_star = (imgp.src.indexOf("star_on") == -1); 
    imgp.src = is_star ? "images/star_on_2.gif" : "images/star_off_2.gif";
}

function check_alive(divid, url, search_url) {
    downloadUrl('check_alive.php?divid=' + divid + '&url=' + url + '&search_url=' + search_url, 
        function(data, responseCode) {
            eval(data);
    });
}

</script>
<style>
body {
    font-family: sans-serif;
    font-size: 14px;
    line-height: 1.2em;
}

a:link, a:visited {
    color: #006699;
    text-decoration: none;
}

.titlebar {
    background-color: #718BD6;
    color: white;
    height: 1.5em;
    line-height: 1.5em;
    padding: 0.2em 0.3em;
    margin-top: 5px;
}

.titlebar a:link, .titlebar a:visited {
    color: white;
}

.pagenav {
    font-size: 12px;
    background-color: #E0E0E0;
    text-align: left;
    padding: 0 5px 0 5px; 
}

.post {
    border: 1px solid #CCCCCC; 
    border-top: 0px;
    line-height: 1.2em;
    padding: 3px;
    margin-bottom: 10px;
}
</style>
</head>
<body>
<?php

$per_page = 10;

$staronly = @$_GET['staronly'] == "true";
$page_no = isset($_GET['page']) ? $_GET['page'] : 1;

$starlist = load_starlist();

$list = file($LIST_FILE);

if ($staronly) {
    $list_index = filter_list_by_star($list, $starlist);
}

$count = count($list);
if ($count == 0) exit();

$total_page = ceil($count / $per_page);
if ($page_no < 1) $page_no = 1;
if ($page_no > $total_page) $page_no = $total_page;

$from = $count - $per_page * ($page_no - 1) - 1;
$to = $count - $per_page * $page_no;
if ($to < 0) $to = 0;

show_page_nav($page_no, $total_page, $staronly);

$alive_script = "";

for($i=$from; $i>=$to; $i--) {
    if ($staronly) {
        $star_icon = "images/star_on_2.gif";
        $star_id = $list_index[$i];
    } else {
        $star_icon = in_array($i, $starlist) ? "images/star_on_2.gif" : "images/star_off_2.gif"; 
        $star_id = $i;
    }
    
    list($ts, $filename, $att_count) = explode("|", trim($list[$i]));
    list($board, $id) = explode("-", $filename);

    $url = "http://www.newsmth.net/bbstcon.php?board=$board&gid=$id";

    $post = file_get_contents("post/".$filename);
    if (strlen($post) == 0) continue;

    preg_match("|发信人: ([\w\d]+) \((.*)\), 信区: (\w+)<br />标  题: (.*)<br />|U", $post, $m);

    $search_url = "http://www.newsmth.net/bbsbfind.php?q=1&board={$m[3]}&title=".urlencode(iconv("utf-8", "gb2312", $m[4]))."&title2=&title3=&userid=&dt=1000";
    
    $post = preg_replace("|发信人: ([\w\d]+) \((.*)\), 信区: (\w+)<br />标  题: (.*)<br />|Ue", "'发信人: <a href=\"http://www.newsmth.net/bbsqry.php?userid=$1\">$1 ($2)</a>, 信区: <a href=\"http://www.newsmth.net/bbsdoc.php?board=$3\">$3</a><br />标  题: <a href=\"$url\">$4</a> <a href=\"http://www.newsmth.net/bbsbfind.php?q=1&board=$3&title='.urlencode(iconv(\"utf-8\", \"gb2312\", \"$4\")).'&title2=&title3=&userid=&dt=1000\">[Search]</a><br />'", $post, 1);

    $alive_script .= "check_alive('alive{$i}', '".urlencode($url)."', '".urlencode($search_url)."');\n";

    echo "<div class='titlebar'><img src='$star_icon' onclick='toggle_star({$star_id}, this)' /> [".($i+1)."] Archived on ".date("Y-m-d H:i:s", $ts)." [<span id='alive{$i}'>Checking...</span>] </div>";

    echo "<div class='post'>$post";
   
    if ($att_count > 0) {
        echo "<div class='att'>";
        $att_files = glob("att/{$filename}-*");
      
        foreach($att_files as $att_file) {
            echo "<a href='$att_file'>[{$att_file}]</a> "; 
        }
        echo "</div>";
    }

    echo "</div>\n";
}

show_page_nav($page_no, $total_page, $staronly);

echo "<script language='javascript'>$alive_script</script>";

?>
</body>
</html>
<?php 

function show_page_nav($page_no, $total_page, $staronly) {
    $staronly_url = $staronly ? "&staronly=true" : "";
    $staronly_toggle = $staronly ? "index.php" : "index.php?staronly=true";
    $staronly_toggle_icon = $staronly ? "images/star_off_sm_2.gif" : "images/star_on_sm_2.gif";

    $head_link = ($page_no == 1) ? "First" : "<a href='?page=1{$staronly_url}'>First</a>";
    $prev_link = ($page_no == 1) ? "Prev" : "<a href='?page=".($page_no-1)."{$staronly_url}'>Prev</a>";
    $next_link = ($page_no == $total_page) ? "Next" : "<a href='?page=".($page_no+1)."{$staronly_url}'>Next</a>";
    $tail_link = ($page_no == $total_page) ? "Last" : "<a href='?page=$total_page{$staronly_url}'>Last</a>";
    
    $last_update = date("Y-m-d H:i:s", filemtime("./update_log"));

    echo "<div class='pagenav'><span style=\"float:right;\"><strong> NewSMTH Top 10 Archives </strong>[Last update: $last_update]</span> [$head_link] [$prev_link] $page_no of $total_page [$next_link] [$tail_link] [<a href='$staronly_toggle'><img style='border:0' src='$staronly_toggle_icon' /></a>]</div>";   
}

?>
