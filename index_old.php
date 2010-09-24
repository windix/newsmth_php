<?php

putenv("TZ=Australia/Melbourne");

header("Content-Type:text/html;charset=UTF-8");
?>
<html>
<head>
<title>NewSMTH Top10 Archives</title>
<link rel="shortcut icon" href="favicon.ico" />
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

$page_no = isset($_GET['page']) ? $_GET['page'] : 1;

$list = file("list.txt");
$count = count($list);

$total_page = ceil($count / $per_page);
if ($page_no < 1) $page_no = 1;
if ($page_no > $total_page) $page_no = $total_page;

$from = $count - $per_page * ($page_no - 1) - 1;
$to = $count - $per_page * $page_no;
if ($to < 0) $to = 0;

show_page_nav($page_no, $total_page);

for($i=$from; $i>=$to; $i--) {
    list($ts, $filename, $att_count) = explode("|", trim($list[$i]));
    list($board, $id) = explode("-", $filename);

    $url = "http://www.newsmth.net/bbstcon.php?board=$board&gid=$id";

    $post = file_get_contents("post/".$filename);
    if (strlen($post) == 0) continue;
    
    echo "<div class='titlebar'>[".($i+1)."] Archived on ".date("Y-m-d H:i:s", $ts)."</div>";
    
    $post = preg_replace("|发信人: ([\w\d]+) \((.*)\), 信区: (\w+)<br />标  题: (.*)<br />|Ue", "'发信人: <a href=\"http://www.newsmth.net/bbsqry.php?userid=$1\">$1 ($2)</a>, 信区: <a href=\"http://www.newsmth.net/bbsdoc.php?board=$3\">$3</a><br />标  题: <a href=\"$url\">$4</a> <a href=\"http://www.newsmth.net/bbsbfind.php?q=1&board=$3&title='.urlencode(iconv(\"utf-8\", \"gb2312\", \"$4\")).'&title2=&title3=&userid=&dt=7\">[Search]</a><br />'", $post, 1);

    echo "<div class='post'>$post";
   
    if ($att_count > 0) {
        echo "<br />".$att_count." att/{$filename}-*";
        echo "<div class='att'>";
        $att_files = glob("att/{$filename}-*");
      
        foreach($att_files as $att_file) {
            echo "<a href='$att_file'>[{$att_file}]</a> "; 
        }
        echo "</div>";
    }

    echo "</div>";
}

show_page_nav($page_no, $total_page);

?>
</body>
</html>
<?php 
function show_page_nav($page_no, $total_page) {
    $head_link = ($page_no == 1) ? "First" : "<a href='?page=1'>First</a>";
    $prev_link = ($page_no == 1) ? "Prev" : "<a href='?page=".($page_no-1)."'>Prev</a>";
    $next_link = ($page_no == $total_page) ? "Next" : "<a href='?page=".($page_no+1)."'>Next</a>";
    $tail_link = ($page_no == $total_page) ? "Last" : "<a href='?page=$total_page'>Last</a>";
    
    echo "<div class='pagenav'> [$head_link] [$prev_link] $page_no of $total_page [$next_link] [$tail_link] <span style=\"float:right\"> NewSMTH Top 10 Archives </span> </div>";   
}

?>
