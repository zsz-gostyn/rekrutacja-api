<?php

$dsn = 'mysql:host=rekrutacja-mysql;dbname=rekrutacja';
$user = 'rekrutacja';
$password = 'rekrutacja';

$pdo = new PDO($dsn, $user, $password);
echo 'Wersja MySQL: ' . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
