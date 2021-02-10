<?php

function ajax($url, $data = [], $method = 'GET')
{

    $final_url = $url;

    $ch = curl_init();
    if ($method == 'POST') {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    } else {
        $final_url = $url . '?' . http_build_query($data);
    }

    curl_setopt($ch, CURLOPT_URL, $final_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    curl_close($ch);

    $result = json_decode($response, true);

    if (json_last_error() === JSON_ERROR_NONE) {
        return $result;
    } else {
        return $result;
    }
}
