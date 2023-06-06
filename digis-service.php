<?php

class OptionSets
{
    const Currency = [
        "USDC" => "USDC",
        "USDT" => "USDT",
        "EURT" => "EURT",
        "ETH" => "ETH",
        "SOL" => "SOL",
    ];

    const PaymentProfileType = [
        "Ethereum" => "Ethereum",
        "Arbitrum" => "Arbitrum",
        "Solana" => "Solana",
        "Goerli" => "Goerli",
        "Polygon" => "Polygon",
    ];
}

class CryptoCurrency
{
    const USDT = 1;
    const USDC = 2;
    const SOL = 3;
    const ETH = 4;
    const EURT = 5;
}

class FiatCurrency {
    const USD = 1;
    const EUR = 2;
}

class CryptoNetwork
{
    const ETH = 1;
    const SOL = 2;
    const ARB = 3;
    const Polygon = 4;
    const Goerli = 5;
}


$apiHost = "https://app.digis.io/api";

function toCryptoCurrency($c)
{
    switch ($c) {
        case OptionSets::Currency["USDC"]:
            return CryptoCurrency::USDC;
        case OptionSets::Currency["USDT"]:
            return CryptoCurrency::USDT;
        case OptionSets::Currency["EURT"]:
            return CryptoCurrency::EURT;
        case OptionSets::Currency["ETH"]:
            return CryptoCurrency::ETH;
        case OptionSets::Currency["SOL"]:
            return CryptoCurrency::SOL;
    }

    throw new Exception("could not map to crypto currency");
}

function toCryptoNetwork($c)
{
    switch ($c) {
        case OptionSets::PaymentProfileType["Ethereum"]:
            return CryptoNetwork::ETH;
        case OptionSets::PaymentProfileType["Arbitrum"]:
            return CryptoNetwork::ARB;
        case OptionSets::PaymentProfileType["Solana"]:
            return CryptoNetwork::SOL;
        case OptionSets::PaymentProfileType["Goerli"]:
            return CryptoNetwork::Goerli;
        case OptionSets::PaymentProfileType["Polygon"]:
            return CryptoNetwork::Polygon;
    }

    throw new Exception("could not map to crypto network");
}



function getCallbackUrl($uuid, $key)
{
    global $apiHost;
    $path = "/invoice/public/pay/callback";
    $q = http_build_query(["uuid" => $uuid, "key" => $key]);

    return $apiHost . $path . "?q=" . $q;
}


function makeRequest($path, $payload, $apiKey) {
    global $apiHost;
    $headers = ["Content-Type" => "application/json", "API-TOKEN" => $apiKey];
    $apiEndpoint = $apiHost . $path;

    error_log(print_r($payload ,true));

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


function createGetReceiver($address, $network, $apiKey) {
    $payload = [
        "address" => $address,
        "network" => $network,
        "label" => "from Woo Commerce",
    ];

    return makeRequest( "/address/create", $payload, $apiKey);
}

function createTransaction($params, $apiKey)
{
    $uuid = $params["uuid"];
    $key = $params["key"];
    $label = $params["label"];
    $amount = $params["amount"];
    $address = $params["address"];
    $network = $params["network"];
    $currency = $params["currency"];
    $fiatCurrency = $params['fiatCurrency'];

    // Get receiver id
    $receiver = createGetReceiver($address, $network, $apiKey);

    $callbackUrl = getCallbackUrl($uuid, $key);

    $payload = [
        "label" => $label,
        "receiver" => $receiver,
        "currency" => $currency,
        "amount" => (float) $amount,
        "fiatCurrency" => $fiatCurrency,
        "callbackUrl" => $callbackUrl,
    ];

    $result = makeRequest( "/transaction/create", $payload, $apiKey);

    //error_log('HERE');
    //error_log(print_r($result ,true));

    return ["id" => $result["id"], "url" => $result["url"]];
    
}



?>


