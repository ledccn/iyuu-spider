<?php

use Iyuu\Spider\Api\Client;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$api = new Client('');
print_r($api->getSites());
