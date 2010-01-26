<?php
require("star.php");

//echo "<pre>";

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $starlist = load_starlist();
    
    //print_r($starlist);
    
    if (in_array($id, $starlist)) {
        $k = array_search($id, $starlist);
        array_splice($starlist, $k, 1);
        
        //echo "-- REMOVE $id\n --";

    } else {
        $starlist[] = $id;

        //echo "-- INSERT $id\n --";
    }
    
    //print_r($starlist);

    save_starlist($starlist);
}

?>
