<?php

use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Factory\ResponseFactory;
use App\Core\ResponseEmitter;

require __DIR__ . '/../app/vendor/autoload.php';
$config = require __DIR__ . '/../app/config.php';

// создадим серверный запрос из суперглобальных массивов PHP
$request = ServerRequestFactory::createFromGlobals();

// получим метод запроса
// $method = $request->getMethod();
// получим цель запроса
// $path = $request->getUri()->getPath();

// создадим тело как поток
$body = (new StreamFactory)->createStream('Hello, world!');
// создадим ответ, с кодом статуса 200 и телом $body
$response = (new ResponseFactory)->createResponse(200)->withBody($body);

// выведем ответ
(new ResponseEmitter())->emit($response);
