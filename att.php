<?php

$filename = $_GET['filename'];
if (!$filename) die;

$att_files = glob("att/{$filename}-*");
natsort($att_files);

$image_ext_name = array('jpg', 'jpeg', 'png', 'gif', 'bmp');

foreach($att_files as $att_file) {
  $path_parts = pathinfo($att_file);
  $ext_name = strtolower($path_parts['extension']);

  if (in_array($ext_name, $image_ext_name)) {
    echo "<img src='$att_file' />";
  } else {
    echo "<a href='$att_file'>[{$att_file}]</a>"; 
  }

  echo "<br /><br />";
}
