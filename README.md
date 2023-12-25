# yandex-search-api

Library for Yandex Search API

![Packagist Version (custom server)](https://img.shields.io/packagist/v/starkeen/yandex-search-api)
![Packagist PHP Version Support (specify version)](https://img.shields.io/packagist/php-v/starkeen/yandex-search-api)
![GitHub](https://img.shields.io/github/license/starkeen/yandex-search-api)
[![codecov](https://codecov.io/gh/starkeen/yandex-search-api/branch/main/graph/badge.svg)](https://codecov.io/gh/starkeen/yandex-search-api)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=starkeen_yandex-search-api&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=starkeen_yandex-search-api)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/6a91442a3a44406b9d16f7c9c3a2ec24)](https://app.codacy.com/gh/starkeen/yandex-search-api/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)


## Description

This library is a convenient way to interact with the Yandex Search API. It allows for easy integration of Yandex search functionality into your PHP projects. The library encapsulates the complex logic of interacting with the API and provides a simple and understandable interface for executing search queries.

## Installation

To install the library, add it to your project using Composer:

```bash
composer require starkeen/yandex-search-api
```

## Usage

To use the library, you need to get an API key.
You can get it on the [Yandex Search API](https://console.cloud.yandex.ru/) page.

```php
<?php

require_once 'vendor/autoload.php';

use YandexSearch\SearchException;
use YandexSearch\SearchRequest;
use YandexSearch\YandexSearchService;

// Create a guzzlehttp client instance
$httpClient = new \GuzzleHttp\Client();

// Create a logger instance (any of LoggerInterface implementations)
$logger = new \Psr\Log\NullLogger();

// Initialize the client with your API key
$client = new YandexSearchService($httpClient, $logger);
$client->setApiId('abcdefg'); // Folder ID from your Yandex Cloud account
$client->setApiKey('A1B2C3D4'); // API key from your Yandex Cloud account

// Your search query
$query = 'Кому на Руси жить хорошо?';
$searchRequest = new SearchRequest($query);

try {
    $response = $client->search($searchRequest);
    // Process the results
    foreach ($response->getResults() as $result) {
        echo 'Title: ' . $result->getTitle() . PHP_EOL;
        echo 'URL: ' . $result->getUrl() . PHP_EOL;
        echo 'Snippet: ' . $result->getSnippet() . PHP_EOL;
    }
} catch (SearchException $e) {
    echo $e->getMessage();
}
```

## Configuration
To use the library, you need to provide your Yandex Search API key and your account ID.
You can obtain it by registering your application on the [Yandex Cloud Console](https://console.cloud.yandex.ru/).

You can set the API key in the code as follows:

```php
$client = new YandexSearchService($httpClient, $logger);
$client->setApiId('abcdefg'); // Folder ID from your Yandex Cloud account
$client->setApiKey('A1B2C3D4'); // API key from your Yandex Cloud account
```
It is also recommended to store the key in a configuration file and use it when creating an instance of the client.

## Questions and Feedback
If you have questions, issues, or suggestions for improvement,
please create a new issue in the [Issues](https://github.com/starkeen/yandex-search-api/issues) section on GitHub.

## License
This library is distributed under the MIT license.
See the [LICENSE](https://github.com/starkeen/yandex-search-api?tab=MIT-1-ov-file#readme) file for details.
