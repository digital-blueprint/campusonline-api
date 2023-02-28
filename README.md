# CAMPUSonline API Client

[GitHub](https://github.com/digital-blueprint/campusonline-api) |
[Packagist](https://packagist.org/packages/dbp/campusonline-api)

[![Test](https://github.com/digital-blueprint/campusonline-api/actions/workflows/test.yml/badge.svg)](https://github.com/digital-blueprint/campusonline-api/actions/workflows/test.yml)

The goal of this package is to provide a PHP API for the various web services
provided by [CAMPUSonline](https://www.campusonline.tugraz.at).

```php
<?php

use Dbp\CampusonlineApi\Rest\Api;

$api = new Api('https://qline.example.at/online/', 'client_id', 'client_secret');
$ucard = $api->UCard();
$ucard->getCardsForIdentIdObfuscated('1234567890');
```
