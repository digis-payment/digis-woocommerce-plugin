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

class FiatCurrency
{
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

?>