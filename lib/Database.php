<?php
class Database
{
    /**
     * Returns a PDO connection using environment variables.
     */
    public static function getConnection(): PDO
    {
        static $pdo = null;
        if ($pdo === null) {
            $host = getenv('DB_HOST');
            $dbname = getenv('DB_NAME');
            $user = getenv('DB_USER');
            $pass = getenv('DB_PASS');

            if (!$host || !$dbname || !$user) {
                throw new RuntimeException('Database environment variables not set');
            }

            $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $pdo;
    }
}

function connect_db(): PDO {
    return Database::getConnection();
}

