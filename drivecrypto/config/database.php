<?php

$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '5432';
$dbname = getenv('DB_NAME') ?: 'drivecrypto';
$user = getenv('DB_USER') ?: 'postgres';
$password = getenv('DB_PASSWORD');

if ($password === false) {
    die('La variable d\'environnement DB_PASSWORD est obligatoire.');
}

try {
    $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $port, $dbname);
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage());
    die('Erreur de connexion à la base de données.');
}
