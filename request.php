<?php

function makeRequest($path, $payload, $apiKey)
{
    $apiHost = "https://app.digis.io/api";
    $headers = ["Content-Type" => "application/json", "API-TOKEN" => $apiKey];
    $apiEndpoint = $apiHost . $path;

    error_log(print_r($payload, true));

    $body = json_encode($payload);

    $ch = curl_init($apiEndpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt(
        $ch,
        CURLOPT_HTTPHEADER,
        array_map(
            function ($key, $value) {
                return "$key: $value";
            },
            array_keys($headers),
            $headers
        )
    );

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

?>