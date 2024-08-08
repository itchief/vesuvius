<?php

require __DIR__ . '/../app/vendor/autoload.php';
$config = require __DIR__ . '/../app/config.php';

use App\Core\Connection;

try {
    $connection = new Connection($config['db']);
    $statement = $connection->run('TRUNCATE users');

    $id = $connection->insertAndGetId('INSERT INTO users (name, email, created_at, updated_at) VALUES (:name, :email, :created_at, :updated_at)', [
        'name' => 'Leo',
        'email' => 'leo@mail.com',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);

    $row = $connection->selectOne('SELECT * FROM users WHERE id = :id', [
        'id' => $id
    ]);

    if (is_array($row)) {
        echo '<pre>';
        print_r($row);
        echo '</pre>';
    }

} catch (PDOException $e) {
    echo $e;
}
