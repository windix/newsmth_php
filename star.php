<?php

$STAR_FILE = "star.txt";

function load_starlist() {
    global $STAR_FILE;
    return unserialize(file_get_contents($STAR_FILE));
}

function save_starlist($starlist) {
    global $STAR_FILE;
    
    sort($starlist);
    file_put_contents($STAR_FILE, serialize($starlist));
}

function filter_list_by_star(&$list, $starlist) {
    $newlist = array();
    $newlist_index = array();
    $count = count($list);

    for($i = 0; $i < $count; $i++) {
        if (in_array($i, $starlist)) {
            $newlist[] = $list[$i];
            $newlist_index[] = $i;
        }
    }

    $list = $newlist;
    return $newlist_index;
}

?>
