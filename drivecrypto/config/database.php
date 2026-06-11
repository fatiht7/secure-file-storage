<?php

$env = [];
$env_file = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env';

if (is_file($env_file)) {
    $env = parse_ini_file($env_file, false, INI_SCANNER_RAW);

    if ($env === false) {
        die('Unable to read the .env configuration file.');
    }
}

function config_value(string $name, ?string $default = null): ?string
{
    global $env;

    $value = getenv($name);
    if ($value !== false && $value !== '') {
        return $value;
    }

    return isset($env[$name]) && $env[$name] !== '' ? $env[$name] : $default;
}

$host = config_value('DB_HOST', 'localhost');
$port = config_value('DB_PORT', '5432');
$dbname = config_value('DB_NAME', 'drivecrypto');
$user = config_value('DB_USER', 'postgres');
$password = config_value('DB_PASSWORD');

if ($password === null) {
    die('DB_PASSWORD must be configured in the environment or in the .env file.');
}

try {
    $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $port, $dbname);
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage());
    die(current_language() === 'en'
        ? 'Unable to connect to the database.'
        : 'Erreur de connexion à la base de données.');
}
