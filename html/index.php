<?php

use App\Core\ResponseEmitter;

use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Factory\ResponseFactory;

require __DIR__ . '/../app/vendor/autoload.php';
$config = require __DIR__ . '/../app/config.php';

$request = ServerRequestFactory::createFromGlobals();
$response = (new ResponseFactory)->createResponse();

$body = (new StreamFactory)->createStream('Hello, world!');
$response = $response->withBody($body);

$responseEmitter = new ResponseEmitter();
$responseEmitter->emit($response);
