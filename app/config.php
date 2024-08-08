<?php

return [
    'db' => [
        'database' => 'app',
        'user' => 'root',
        'password' => 'root',
        'host' => 'mysql',
        'port' => '3306',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'options' => [
            PDO::ATTR_PERSISTENT => true,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'
        ]
    ],
];
