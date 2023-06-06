<?php
require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/request.php';

function getCallbackUrl($uuid, $key)
{
    $site_url = get_site_url();
    $path = "/checkout/order-received";
    $id = substr($uuid, 1); // remove first char
    $q = http_build_query([
        "order" => $id,
        "key" => $key,
        "digis_gateway_result" => "success",
    ]);

    return $site_url . $path . "?" . $q;
}

function createGetReceiver($address, $network, $apiKey)
{
    $payload = [
        "address" => $address,
        "network" => $network,
        "label" => "from Woo Commerce",
    ];

    return makeRequest("/address/create", $payload, $apiKey);
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
    $fiatCurrency = $params["fiatCurrency"];

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

    $result = makeRequest("/transaction/create", $payload, $apiKey);

    //error_log('HERE');
    //error_log(print_r($result ,true));

    return ["id" => $result["id"], "url" => $result["url"]];
}
?>


