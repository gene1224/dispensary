<?php

function ajax($url, $data = [], $method = 'GET')
{

    $ch = curl_init();

    $final_url = $method == 'GET' ? $url . '?' . http_build_query($data) : $url . '?action=' . $data['action'];

    curl_setopt($ch, CURLOPT_URL, $final_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    if ($method == 'POST') {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('data' => urlencode(json_encode($data))));
    }

    $response = curl_exec($ch);

    curl_close($ch);

    $result = json_decode($response, true);

    if (json_last_error() === JSON_ERROR_NONE) {
        return $result;
    } else {
        return $response;
    }
}
