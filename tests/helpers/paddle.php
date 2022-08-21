<?php

function generatePaddleSignature(array $fields) {
    ksort($fields);
    foreach ($fields as $k => $v) {
        if (! in_array(gettype($v), ['object', 'array'])) {
            $fields[$k] = "$v";
        }
    }

    $private_key = openssl_pkey_new();
    $public_key_pem = openssl_pkey_get_details($private_key)['key'];
    $public_key = openssl_pkey_get_public($public_key_pem);

    config(['services.paddle.public_key' => $public_key]);

    openssl_sign(
        serialize($fields),
        $signature,
        $private_key
    );

    return base64_encode($signature);
}

function getPaddleWebhookParams(array $fields) {
    $fields['p_signature'] = generatePaddleSignature($fields);
    return $fields;
}