# CAMPUSonline API Client

[GitHub](https://github.com/digital-blueprint/campusonline-api) |
[Packagist](https://packagist.org/packages/dbp/campusonline-api)

[![Test](https://github.com/digital-blueprint/campusonline-api/actions/workflows/test.yml/badge.svg)](https://github.com/digital-blueprint/campusonline-api/actions/workflows/test.yml)

The goal of this package is to provide a PHP API for the various web services
provided by [CAMPUSonline](https://www.campusonline.tugraz.at).

```
composer require dbp/campusonline-api
```

## Legacy Rest API

```php
<?php

use Dbp\CampusonlineApi\Rest\Api;

$api = new Api('https://qline.example.at/online/', 'client_id', 'client_secret');
$ucard = $api->UCard();
$ucard->getCardsForIdentIdObfuscated('1234567890');
```

## Generic Exports API

```php
<?php

use Dbp\CampusonlineApi\Rest\Api;

$api = new Api('https://qline.example.at/online/', 'client_id', 'client_secret');
$generic = $api->GenericApi('loc_apiMyExport');
$generic->getResource('ID', '42);
```

## Legacy XML Web Services API

```php
<?php

use Dbp\CampusonlineApi\LegacyWebService\Api;

$api = new Api('https://qline.example.at/online/', 'api_token');
$org = $api->OrganizationUnit();
$org->getOrganizationUnitById('1234');
```

## Public Rest API

TODO
