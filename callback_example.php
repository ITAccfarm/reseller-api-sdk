<?php

/*
|-------------------------------------------------------------------
| Callback example
|-------------------------------------------------------------------
|
| This file is going to be you route processing callbacks
| from Accfarm. This is the example of how you would check
| if signature is valid. It's important to use this specific
| function and hash_equals() to check signature validity!
|
*/

function signCallbackData(string $secret, array $data)
{
    ksort($data);

    $string = '';

    foreach($data as $value) {
        if (in_array(gettype($value), ['array', 'object', 'NULL']) ){
            continue;
        }
        if(is_bool($value) && $value){
            $string .= 1;
        } else {
            $string .= $value;
        }
    }

    return hash_hmac('sha512', strtolower($string), $secret);
}

$json = file_get_contents('php://input');
$data = json_decode($json);
$headers = getallheaders();

$secret = 'my_secret';
$testSignature = signCallbackData($secret, $data);
$signature = $headers['Signature'];

if (!hash_equals($signature, $testSignature)) {
    // Error, wrong signature
    die;
}

// Process data
// ...