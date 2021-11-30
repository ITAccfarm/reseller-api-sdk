<?php

/*
|-------------------------------------------------------------------
| Reseller Api SDK use EXAMPLE
|-------------------------------------------------------------------
|
| This file is api use example! It contains list of all methods with
| ways to use them. Data in these examples are invalid and used
| only to show how to work with SDK.
|
| You can delete this file, after you examined what you need!
|
*/

require 'src\ResellerSDK\ResellerSDK.php';
use ITAccfarm\ResellerSDK\ResellerSDK;

$api = new ResellerSDK();

/*
 * ------------------------
 * ----------Auth----------
 * ------------------------
 */

$authData = $api->auth('reseller@email.com', 'reseller@email.com');
//$refreshSuccess = $api->refresh();
//$invalidateSuccess = $api->invalidate();

if (empty($authData)) {
    die();
}

$userSecret = $api->getSecret();
$bearerToken = $api->getToken();

//$api
//    ->setSecret('secret')
//    ->setToken('token');

/*
 * ------------------------
 * ----------Data----------
 * ------------------------
 */

$user = $api->user();

$orders = $api->orders();
$order = $api->order('order_number');

$categories = $api->categories();

$offersByCategory = $api->offers([
    'category_id' => 3
]);

$offersByProduct = $api->offers([
    'product_id' => 12
]);

$offersByDiscount = $api->offers([
    'discount' => 1 // true
]);

$offers = $api->offers([
    'category_id' => 7,
    'product_id' => 21,
    'discount' => 1
]);

$offer = $api->offer(200);

/*
 * ------------------------
 * ----------Buy-----------
 * ------------------------
 */

// Simple offer
$api->buy('offer', [
    'quantity' => 20,
    'offer_id' => 142,
    'sandbox' => 1,
]);

// Simple review with AI autogen reviews
$api->buy('review', [
    'quantity' => 100,
    'offer_id' => 200,
    'url' => 'http:\\\\your-pace-for-reviews.com\\', // http:\\your-pace-for-reviews.com\
    'sandbox' => 1,
]);

// Review with pre-written reviews
$api->buy('review', [
    'quantity' => 1000,
    'offer_id' => 200,
    'url' => 'http:\\\\your-pace-for-reviews.com\\',
    'sandbox' => 1,

    // Option 1: In a string
    'reviews' => 'review1\n review2\n review3\n',

    // Option 2: In an array
    //'reviews_array' => ['review1', 'review2', 'review3'],

    // Option 3: In a file
    //'file' => ['full/path/to/file'],
]);

// Simple 'install' without reviews
$api->buy('install', [
    'quantity' => 50,
    'offer_id' => 321,
    'app_link' => 'http:\\\\your-pace-for-reviews.com\\',
    'app_id' => 'some_app_id',
    'days' => 30,
    'country' => 'US',
    'sandbox' => 1,
]);

// 'install' with reviews
$api->buy('install', [
    'quantity' => 50,
    'offer_id' => 321,
    'app_link' => 'http:\\\\your-pace-for-reviews.com\\',
    'app_id' => 'some_app_id',
    'days' => 30,
    'country' => 'US',
    'sandbox' => 1,

    // Option 1: In a string
    'reviews' => 'review1\n review2\n review3\n',

    // Option 2: In a file
    //'file' => ['full/path/to/file'],
]);