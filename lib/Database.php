<?php
class Database
{
    /**
     * Returns a PDO connection using environment variables.
     */
    private static function loadEnv(): void
    {
        $baseDir = dirname(__DIR__);
        $paths = [
            $baseDir . '/.env',
            $baseDir . '/.env.example'
        ];

        foreach ($paths as $envPath) {
            if (!file_exists($envPath)) {
                continue;
            }

            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || $line[0] === '#') {
                    continue;
                }

                if (strpos($line, '=') !== false) {
                    list($name, $value) = array_map('trim', explode('=', $line, 2));
                    if (!getenv($name)) {
                        putenv("{$name}={$value}");
                        $_ENV[$name] = $value;
                        $_SERVER[$name] = $value;
                    }
                }
            }
            // if variables were loaded from .env, stop looking
            if (getenv('DB_HOST') && getenv('DB_NAME') && getenv('DB_USER')) {
                break;
            }
        }

        // If credentials are still missing, set project defaults
        if (!getenv('DB_HOST')) {
            putenv('DB_HOST=db.kosmo.or.kr');
            $_ENV['DB_HOST'] = 'db.kosmo.or.kr';
            $_SERVER['DB_HOST'] = 'db.kosmo.or.kr';
        }
        if (!getenv('DB_NAME')) {
            putenv('DB_NAME=dbbestluck');
            $_ENV['DB_NAME'] = 'dbbestluck';
            $_SERVER['DB_NAME'] = 'dbbestluck';
        }
        if (!getenv('DB_USER')) {
            putenv('DB_USER=bestluck');
            $_ENV['DB_USER'] = 'bestluck';
            $_SERVER['DB_USER'] = 'bestluck';
        }
        if (!getenv('DB_PASS')) {
            putenv('DB_PASS=cmhospital1949!');
            $_ENV['DB_PASS'] = 'cmhospital1949!';
            $_SERVER['DB_PASS'] = 'cmhospital1949!';
        }
    }

    public static function getConnection(): PDO
    {
        static $pdo = null;
        if ($pdo === null) {
            $host = getenv('DB_HOST');
            $dbname = getenv('DB_NAME');
            $user = getenv('DB_USER');
            $pass = getenv('DB_PASS');

            if (!$host || !$dbname || !$user) {
                self::loadEnv();
                $host = getenv('DB_HOST');
                $dbname = getenv('DB_NAME');
                $user = getenv('DB_USER');
                $pass = getenv('DB_PASS');
            }

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

