<?php

function curl_get($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
      echo "*** Curl error: ".curl_error($ch);
    }

    curl_close($ch);
    return $response;
}

?>
