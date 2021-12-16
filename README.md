# CAMPUSonline API Client

```php
<?php

use Dbp\CampusonlineApi\Rest\Api;

$api = new Api('https://qline.example.at/online/', 'client_id', 'client_secret');
$ucard = $api->UCard();
$ucard->getCardsForIdentIdObfuscated('1234567890');
```