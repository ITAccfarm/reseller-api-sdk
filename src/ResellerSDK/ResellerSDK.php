<?php

namespace ITAccfarm\ResellerSDK;

/*
|-------------------------------------------------------------------
| Accfarm reseller Api SDK v1
|-------------------------------------------------------------------
|
| This is the main (and only) class that helps you
| work with Accfarm api. It allows you to basically use all
| api endpoints and possibilities without writing your own SDK.
| Or just to use it as an inspiration or point of reference,
| while writing your own. We wish you best of luck!
|
 */

class ResellerSDK
{
    /**
     * Path to current directory
     *
     * @var string
     */
    private $dirPath;

    /**
     * Base Accfarm api url.
     *
     * @var string
     */
    private $baseUrl;

    /**
     * Authorization bearer token.
     *
     * @var string
     */
    private $bearerToken;

    /**
     * User secret to check callback signature.
     *
     * @var string
     */
    private $userSecret;

    /**
     * Settings from settings.json containing following parameters:
     * ['bearerToken' => '', 'userSecret' => '', 'endpoints' => ['auth' => 'user/login', ...]]
     *
     * @var array
     */
    private $settings;

    /**
     * Api endpoints from settings
     * ['auth' => 'user/login', ...]
     *
     * @var array
     */
    private $endpoints;

    /**
     * Api constructor.
     *
     * 1. Get path to class directory
     * 2. Get settings.
     * 3. Set base URL parameter.
     * 4. Set token parameter.
     * 5. Set endpoints parameter.
     */
    public function __construct()
    {
        $this->dirPath = dirname(__FILE__) . DIRECTORY_SEPARATOR;

        $this->settings = $this->getFileSettings();

        $this->baseUrl = 'https://accfarm.com/api/v1/';
        $this->userSecret = $this->settings['secret'] ?? '';
        $this->bearerToken = $this->settings['bearerToken'] ?? '';
        $this->endpoints = $this->settings['endpoints'];
    }

    /**
     * Authentication for user. Sets bearer token to settings.json file and to parameter.
     *
     * @param string $email
     * @param string $password
     * @return bool
     */
    public function auth(string $email, string $password): bool
    {
        $response = $this->authRequest([
            'email' => $email,
            'password' => $password,
        ]);

        if (empty($response['token']) || empty($response['user'])) {
            return false;
        }

        $this->bearerToken = $response['token'];
        $this->userSecret = $response['user']['secret'] ?? '';

        $this
            ->setSettingsToken()
            ->setSettingsSecret()
            ->setFileSettings();

        return true;
    }

    /**
     * Simply returns user secret for your further callback purposes.
     *
     * @return string
     */
    public function getUserSecret(): string
    {
        return $this->userSecret ?? '';
    }

    /**
     * Returns available offers filtered by options.
     *
     * category_id:  int
     * product_id:   int
     * discount:     bool (false|0 - all or true|1 - only with discounts)
     *
     * Data example: ['category_id' => 1,'product_id' => 15, 'discount' => 1]
     * @param array $data
     * @return array
     */
    public function offers(array $data = []): array
    {
        $requestData = $this->validate($data, [
            'category_id' => 'optional',
            'product_id'  => 'optional',
            'discount'    => 'optional',
        ]);

        return $this->getOffersRequest($requestData);
    }

    /**
     * Returns all available categories to user.
     *
     * @return array
     */
    public function categories(): array
    {
        return $this->getCategoriesRequest();
    }

    /**
     * Returns all user orders
     *
     * @return array
     */
    public function orders(): array
    {
        return $this->getOrdersRequest();
    }

    /**
     * Returns all user orders
     *
     * @param string $orderNumber
     * @return array
     */
    public function order(string $orderNumber): array
    {
        return $this->getOrderRequest(['order_number' => $orderNumber]);
    }

    /**
     * Returns authenticated user data.
     *
     * @return array
     */
    public function user(): array
    {
        $user = $this->getUserRequest();

        $this->userSecret = $user['secret'];

        $this
            ->setSettingsSecret()
            ->setFileSettings();

        return $user;
    }

    /**
     * Refresh bearer token. Token has to be present.
     *
     */
    public function refresh(): bool
    {
        if (empty($this->bearerToken)) {
            return false;
        }

        $token = $this->refreshTokenRequest();

        if (empty($token)) {
            return false;
        }

        $this->bearerToken = $token;

        $this
            ->setSettingsToken()
            ->setFileSettings();

        return true;
    }

    /**
     * Invalidates and unsets token.
     *
     * @return bool
     */
    public function invalidate(): bool
    {
        $success = $this->invalidateTokenRequest();

        if (!$success) {
            return false;
        }

        $this->bearerToken = '';

        $this
            ->setSettingsToken()
            ->setFileSettings();

        return true;
    }

    /**
     * Types:
     * 1. 'offer'
     * 2. 'review'
     * 3. 'install'
     *
     * 'offer' type $data params:
     * - quantity:          required|int
     * - offer_id:          required|int
     * - callback_url:      optional|string
     * - sandbox:           optional|bool
     *
     * 'review' type $data params:
     * - quantity:          required|int
     * - offer_id:          required|int
     * - url:               required|string
     * - reviews_array:     optional|array
     * - reviews:            optional|string
     * - file:              optional|types:txt,doc,csv|max_size:6020
     * - callback_url:      optional|string
     * - sandbox:           optional|bool
     *
     * 'install' type $data params:
     * - quantity:          required|int
     * - offer_id:          required|int
     * - app_link:          required|string
     * - app_id:            required
     * - days:              required|int
     * - country:           required|array
     * - reviews:            optional|string
     * - file:              optional|types:txt,doc,csv|max_size:6020
     * - callback_url:      optional|string
     * - sandbox:           optional|bool
     *
     * If 'file', 'review' or 'reviews_array' is empty, then reviews will be autogenerated.
     * It is recommended to write your on reviews.
     *
     * Params in $data explained:
     *
     * 'file': send reviews in file. They will be reviewed manually.
     * example: $data['file'] = realpath('') . '\' . 'test_data.txt;
     *
     * 'reviews': reviews should be divided by \n.
     * example: $data['review'] = 'review1 \n review2 \n review3';
     *
     * 'reviews_array': each review in new index.
     * example: $data['reviews_array'] = ['review1', 'review2', ...]
     *
     * 'url': url to place where to write reviews
     *
     * 'app_link': url to place where to make installs
     *
     * 'days': spread installations in this span of days
     *
     * 'country': ISO country code (US, RU, UA, etc.)
     *
     * 'sandbox': sandbox mode to make test orders.
     * $data['sandbox'] = 1; for test orders.
     *
     * 'callback_url': your url endpoint to send order data to.
     *
     * @param string $type
     * @param array $data
     *
     * @return ?array
     */
    public function buy(string $type, array $data): ?array
    {
        if ($type == 'offer') {
            $requestData = $this->validate($data, [
                'quantity'      => 'required',
                'offer_id'      => 'required',
                'callback_url'  => 'optional',
                'sandbox'       => 'optional',
            ]);
        } elseif ($type == 'review') {
            $requestData = $this->validate($data, [
                'quantity'      => 'required',
                'offer_id'      => 'required',
                'url'           => 'required',
                'reviews_array' => 'optional',
                'reviews'       => 'optional',
                'file'          => 'optional',
                'callback_url'  => 'optional',
                'sandbox'       => 'optional',
            ]);
        } elseif ($type == 'install') {
            $requestData = $this->validate($data, [
                'quantity'      => 'required',
                'offer_id'      => 'required',
                'app_link'      => 'required',
                'app_id'        => 'required',
                'days'          => 'required',
                'country'       => 'required',
                'reviews'       => 'optional',
                'file'          => 'optional',
                'callback_url'  => 'optional',
                'sandbox'       => 'optional',
            ]);
        } else {
            return null;
        }

        if (!empty($requestData['errors'])) {
            return $requestData;
        }

        return $this->buyRequest($requestData);
    }

    /**
     * Post request to 'https://accfarm.com/api/v1/user'
     *
     * Data example: ['email' => 'email@email.com', 'password' => 'pass']
     *
     * @param array $data
     * @return array
     */
    private function authRequest(array $data): array
    {
        $urn = $this->endpoints['auth'];

        $response = $this->call('POST', $urn, [
            'email' => $data['email'],
            'password' => $data['password'],
        ], false);

        if (empty($response)) {
            return [];
        }

        return $response;
    }

    /**
     * Get request to 'https://accfarm.com/api/v1/offers'
     *
     * category_id: int
     * product_id:  int
     * discount:    bool (0 or 1)
     *
     * Data example: ['category_id' => 1,'product_id' => 15, 'discount' => 1]
     *
     * @param array $data
     * @return array
     */
    private function getOffersRequest(array $data = []): array
    {
        $urn = $this->endpoints['offers'];

        $response = $this->call('GET', $urn, $data);

        if (empty($response)) {
            return [];
        }

        return $response;
    }

    /**
     * Get request to 'https://accfarm.com/api/v1/categories'
     *
     * @return array
     */
    private function getCategoriesRequest(): array
    {
        $urn = $this->endpoints['categories'];

        $response = $this->call('GET', $urn);

        if (empty($response)) {
            return [];
        }

        return $response;
    }

    /**
     * Get request to 'https://accfarm.com/api/v1/orders'
     *
     * @return array
     */
    private function getOrdersRequest(): array
    {
        $urn = $this->endpoints['orders'];

        $response = $this->call('GET', $urn);

        if (empty($response)) {
            return [];
        }

        return $response;
    }

    /**
     * Get request to 'https://accfarm.com/api/v1/order'
     *
     * @param array $data
     * @return array
     */
    private function getOrderRequest(array $data): array
    {
        $urn = $this->endpoints['order'];

        $response = $this->call('GET', $urn, $data);

        if (empty($response)) {
            return [];
        }

        return $response;
    }

    /**
     * Get request to 'https://accfarm.com/api/v1/user'
     *
     * @return array
     */
    private function getUserRequest(): array
    {
        $urn = $this->endpoints['user'];

        $response = $this->call('GET', $urn);

        if (empty($response)) {
            return [];
        }

        return $response;
    }

    /**
     * Post request to 'https://accfarm.com/api/v1/user/refresh'
     *
     * @return string
     */
    private function refreshTokenRequest(): string
    {
        $urn = $this->endpoints['refresh'];

        $response = $this->call('POST', $urn, [
            'token' => $this->bearerToken,
        ], false);

        if (!empty($response['error']) || empty($response['token'])) {
            return '';
        }

        return $response['token'];
    }

    /**
     * Post request to 'https://accfarm.com/api/v1/user/invalidate'
     *
     * @return bool
     */
    private function invalidateTokenRequest(): bool
    {
        $urn = $this->endpoints['invalidate'];

        $response = $this->call('POST', $urn, [
            'token' => $this->bearerToken,
        ], false);

        if (empty($response['error']) && !empty($response['msg']) && $response['msg'] == 'Token invalidated') {
            return true;
        }

        return false;
    }

    /**
     * @param array $data
     * @return array
     */
    private function buyRequest(array $data): array
    {
        $urn = $this->endpoints['buy'];

        $response = $this->call('POST', $urn, $data);

        if (empty($response)) {
            return [];
        }

        return $response;
    }

    /**
     * General curl api call method.
     *
     * Available methods: 'GET', 'POST'
     * URN example: 'user/login'
     * Data example: ['email' => 'blablabl@dev.null', 'password' => 'my_password']
     *
     * @param string $method
     * @param string $urn
     * @param array $data
     * @param bool $useToken
     *
     * @return array|null
     */
    private function call(string $method, string $urn, array $data = [], bool $useToken = true): ?array
    {
        $url = $this->baseUrl . $urn;
        $curlParams = [];
        $headers = [];

        // Check method
        if ($method == 'GET') {
            $url = !empty($data)
                ? sprintf("%s?%s", $url, http_build_query($data))
                : $url;

            $headers[] = 'Content-Type: application/json';
        } elseif ($method == 'POST') {
            $curlParams += [
                CURLOPT_POST => 1,
            ];
        }

        // Check file sending required
        if ($method == 'POST' && empty($data['file'])) {
            $curlParams += [
                CURLOPT_POSTFIELDS => json_encode($data),
            ];

            $headers[] = 'Content-Type: application/json';
        } elseif ($method == 'POST' && !empty($data['file'])) {
            $postFields = [
                'file' => new CURLFile($data['file'], $this->getFileMimeType($data['file']), 'file'),
            ];

            $postFields += $data;

            $curlParams += [
                CURLOPT_POSTFIELDS => $postFields,
            ];

            $headers[] = 'Content-Type: multipart/form-data';
        }

        // Check if token present and required
        if (!empty($this->bearerToken) && $useToken) {
            $headers[] = 'Authorization: Bearer ' . $this->bearerToken;
        }

        $curlParams += [
            CURLOPT_HEADER => 0,
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_TIMEOUT => 4,
            CURLOPT_FOLLOWLOCATION => 0,
            CURLOPT_HTTPHEADER => $headers,
        ];

        $curl = curl_init($url);
        curl_setopt_array($curl, $curlParams);
        $result = curl_exec($curl);
        curl_close($curl);

        if (empty($result)) {
            return null;
        }

        return json_decode($result, true);
    }

    /**
     * Gets data from settings.json
     *
     * @return array
     */
    private function getFileSettings(): array
    {
        if (!file_exists($this->dirPath . 'settings.json')) {
            return [];
        }

        $settingsJson = file_get_contents($this->dirPath . 'settings.json');

        return json_decode($settingsJson, true) ?? [];
    }

    /**
     *  Clears settings.json file and sets new settings data from settings array.
     *
     * @return void
     */
    private function setFileSettings()
    {
        file_put_contents($this->dirPath . 'settings.json', '');
        file_put_contents($this->dirPath . 'settings.json', json_encode($this->settings, JSON_PRETTY_PRINT));
    }

    /**
     * Sets bearerToken to settings array.
     *
     * @return $this
     */
    private function setSettingsToken(): ResellerSDK
    {
        $this->settings['bearerToken'] = $this->bearerToken;

        return $this;
    }

    /**
     * Sets userSecret to settings array.
     *
     * @return $this
     */
    private function setSettingsSecret(): ResellerSDK
    {
        $this->settings['userSecret'] = $this->userSecret;

        return $this;
    }

    /**
     * @param string $pathToFile
     * @return false|string
     */
    private function getFileMimeType(string $pathToFile)
    {
        return mime_content_type($pathToFile);
    }

    /**
     * Available rules:
     * 1. required or optional
     *
     * @param array $data
     * @param array $rulesArray
     * @return array
     */
    private function validate(array $data, array $rulesArray): array
    {
        $validated = [];
        $errors = [];

        foreach ($rulesArray as $field => $ruleString) {
            $rules = explode('|', $ruleString);

            foreach ($rules as $rule) {
                $rule .= 'Rule';
                $validatedRule = $this->$rule($field, $data);

                if (!empty($validatedRule['error'])) {
                    $errors += [$field => $validatedRule['error']];
                } else {
                    $validated += $validatedRule;
                }
            }
        }

        return !empty($errors)
            ? ['errors' => $errors]
            : $validated;
    }

    /**
     * @param string $field
     * @param array $data
     * @return array
     */
    private function requiredRule(string $field, array $data): array
    {
        if (empty($data[$field])) {
            return ['error' => $field . ' field is required!'];
        }

        return [$field => $data[$field]];
    }

    /**
     * @param string $field
     * @param array $data
     * @return array
     */
    private function optionalRule(string $field, array $data): array
    {
        if (empty($data[$field])) {
            return [];
        }

        return [$field => $data[$field]];
    }
}