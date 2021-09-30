Markdown output:
# Accfarm SDK

This is an official [Accfarm](https://accfarm.com/) Reseller SDK. Out of the box it covers all endpoints and has the functionality to fully interact with API. This SDK can be used for development as it is, or serve as reference for you own SDK.

* [API Reference](https://documenter.getpostman.com/view/2711143/Tzz7QJNw#auth-info-17a939f6-551a-4217-83dd-bddcd305fa34)

---

### Table of contents


* [Installation](#installation)
* [Getting started](#getting-started)
* [Order Statuses](#order-statuses)
* [Methods](#methods)
  * [Authentication](#authentication)
    * [Authenticate](#authenticate)
    * [Refresh](#refresh)
    * [Invalidate](#invalidate)
  * [Data](#data)
    * [Offers](#offers)
    * [Categories](#categories)
    * [Orders](#orders)
    * [Order](#order)
    * [User](#user)
  * [Buy](#buy)
* [Callback](#callback)

---

### Installation

Installation via composer:
`composer require itaccfarm/reseller-api-sdk`

Manual installation:
1. Download archive 
2. Unpack and add ResellerSDK to your project
3. Remove ITAccfarm from namespace and path to where you put ResellerSDK folder

---

### Getting started

There are only 4 files in this SDK:

* src/ResellerSDK/ResellerSDK.php - Contains all core code and logic
* src/ResellerSDK/settings.json - Stores bearer token, user secret and endpoints
* index.php - File used for demonstration (you can delete it)
* callback_example.php - File used for demonstration (you can delete it)

Let's start with our first and only `ResellerSDK` class.

1. First of all, we need to create new object.

```php
require 'src\ResellerSDK\ResellerSDK.php';
// and/or
use ITAccfarm\ResellerSDK\ResellerSDK;

$api = new ResellerSDK();
```

2. Then authenticate with email and password. And check if our credentials are correct.

```php
$success = $api->auth('email@email.com', 'pass');

if (!$success) {
    throw new Exception('Wrong credentials');
}
```

3. Then we can make API requests! You can see the list of all methods in [Methods section](#methods).

```php
$categories = $api->categories();
$offers = $api->offers(['product_id' => 4]);
$user = $api->user();
```

---

### Order statuses
- 1: new: 
- 2: in_progress 
- 3: complete 
- 4: canceled 
- 5: pending 
- 6: refunded 
- 7: mispaid 

---

### Methods

List of all methods:
* `$api->auth(string $email, string $password): bool`
* `$api->refresh(): bool`
* `$api->invalidate(): bool`


* `$api->uesr(): array`
* `$api->categories(): array`
* `$api->offers(array $filters): array`
* `$api->orders(): array`
* `$api->order(string $orderNumber): array`


* `$api->buy(string $type, array $data)`


* `$api->getUserSecret(string $type, array $data): string`

---

### Authentication

### Authenticate

**Endpoint:** [https://accfarm.com/api/v1/user/login](https://accfarm.com/api/v1/user/login)  
**Method:** `$api->auth(string $email, string $password)`  
**Params:** `string $email, string $password`  
**Returns:** bool (authentication successful or not)

This method:

1. Attempts authentication on 'user/login' endpoint.
2. Stores

_bearer token_ to settings.json and to object.

After using this method successfully once _bearer token_ is stored, so you can call any api endpoints after that and don't need to call it every time you want to access some endpoint, until token expires.

I would recommend using it once, like a setup method.

### Refresh

**Endpoint:** [https://accfarm.com/api/v1/user/refresh](https://accfarm.com/api/v1/user/refresh)  
**Method:** `$api->refresh();`  
**Returns: bool \(refresh successful or not\)** null

This method:

1. Refreshes token. 
2. Replaces old token with a new one in settings.json and object.

### Invalidate

**Endpoint:** [https://accfarm.com/api/v1/user/invalidate](https://accfarm.com/api/v1/user/invalidate)  
**Method:** `$api->refresh();`  
**Returns: bool \(refresh successful or not\)** null

This method:
1. Invalidates and deletes token from object and settings.json.

---

### Data

### User

**Endpoint:** [https://accfarm.com/api/v1/user](https://accfarm.com/api/v1/user)  
**Method:** `$api->user();`  
**Returns:** array (current user)

This method:

1. Returns current user.

### Offers

**Endpoint:** [https://accfarm.com/api/v1/offers](https://accfarm.com/api/v1/offers)  
**Method:** `$api->offers(array $data);`  
**Params:** Optional: `[category_id => int, product_id => int, discount => bool]`  
**Returns:** array (offers)

This method:

1. Return offers with filters.

### Categories

**Endpoint:** [https://accfarm.com/api/v1/categories](https://accfarm.com/api/v1/categories)  
**Method:** `$api->categories();`  
**Returns:** array (all categories)

This method:

1. Returns all categories.

### Orders

**Endpoint:** [https://accfarm.com/api/v1/orders](https://accfarm.com/api/v1/orders)  
**Method:** `$api->orders();`  
**Returns:** array (user orders)

This method:

1. Return all user orders.

### Order

**Endpoint:** [https://accfarm.com/api/v1/order](https://accfarm.com/api/v1/order)  
**Method:** `$api->order(string $orderNumber);`  
**Params:** `string $orderNumber`  
**Returns:** array (user order)

This method:

1. Return order by it's 'order_number'.

---

### Buy

**Endpoint:** [https://accfarm.com/api/v1/buy](https://accfarm.com/api/v1/buy)  
**Method:** `$api->buy(string $type, array $data);`  
**Returns:** array (user order)


This method:

1. Creates order and does everything a simple order creation on website would do.


**Params for types:** 

`$type` options: 
1. `'offer'`
2. `'review'`
3. `'install'`

`'offer'` type `$data` params:

* quantity:          **required**|int
* offer_id:          **required**|int
* callback_url:      optional|string
* sandbox:           optional|bool
  
`'review'` type `$data` params:

* quantity:          **required**|int
* offer_id:          **required**|int
* url:               **required**|string
* reviews_array:     optional|array
* reviews:            optional|string
* file:              optional|types:txt,doc,csv|max_size:6020
* callback_url:      optional|string
* sandbox:           optional|bool
  
`'install'` type `$data` params:

* quantity:          **required**|int
* offer_id:          **required**|int
* app_link:          **required**|string
* app_id:            **required**
* days:              **required**|int
* country:           **required**|array
* reviews:            optional|string
* file:              optional|types:txt,doc,csv|max_size:6020
* callback_url:      optional|string
* sandbox:           optional|bool

If `'file'`, `'reviews'` or `'reviews_array'` is empty, then reviews will be autogenerated.
It is recommended to write your on reviews.

**Params in** `$data` **explained:**

* `'file'`: send reviews in file. They will be reviewed manually.
Example: `$data['file'] = realpath('') . '\' . 'test_data.txt;`
* `'reviews'`: reviews should be divided by \n.
Example: `$data['reviews'] = 'review1 \n review2 \n review3';`
* `'reviews_array'`: each review in new index.
Example: `$data['reviews_array'] = ['review1', 'review2', ...]`
* `'url'`: url to place where to write reviews
* `'app_link'`: url to place where to make installs
* `'days'`: spread installations in this span of days
* `'country'`: ISO country code (US, RU, UA, etc.)
* `'sandbox'`: sandbox mode to make test orders.
`$data['sandbox'] = 1;` for test orders.
* `'callback_url'`: your url endpoint to send order data to.
---

### Callback

If you provide callback_url in buy method, 
Accfarm will send you order data on order updates.

```php
$response = [
  'number' => 'order_number',
  'status' => 'status_id',
  'total' => 'price',
  'secret_key' => 'secret_key'           
  'download_link' - 'link' // if needed
];
```

Call to you endpoint (your callback_url) will always have _**Signature**_ header.
This will allow you to be sure call is coming from Accfarm.
To check if it's valid you're going to need to: 
1. Hash request data with your user secret with the following code:

```php
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
```

2. And then to check resulting hash against **_Signature_** header:

```php
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
```