<?php

namespace config;

use Dotenv\Dotenv;
use PDO;
use PDOException;

/**
 * Class Config
 * Configuración global de la aplicación (patrón Singleton).
 * Carga las variables del .env y crea la única conexión PDO a PostgreSQL.
 */
class Config
{
    private static $instance;

    private $postgresDb;
    private $postgresUser;
    private $postgresPassword;
    private $postgresHost;
    private $postgresPort;
    private $db;

    private $rootPath;
    private $uploadPath;
    private $uploadUrl;

    private function __construct()
    {
        // Raíz del proyecto: src/config -> src -> raíz (donde está el .env)
        $this->rootPath = dirname(__DIR__, 2) . '/';
        $this->uploadPath = dirname(__DIR__) . '/uploads/';

        // safeLoad() no lanza error si no existe el .env
        $dotenv = Dotenv::createImmutable($this->rootPath);
        $dotenv->safeLoad();

        $this->postgresDb = $this->env('POSTGRES_DB', 'tienda');
        $this->postgresUser = $this->env('POSTGRES_USER', 'admin');
        $this->postgresPassword = $this->env('POSTGRES_PASSWORD', '');
        $this->postgresHost = $this->env('POSTGRES_HOST', 'localhost');
        $this->postgresPort = $this->env('POSTGRES_PORT', '5432');
        $this->uploadUrl = 'http://localhost:' . $this->env('APP_PORT', '8080') . '/uploads/';

        try {
            $this->db = new PDO(
                "pgsql:host={$this->postgresHost};port={$this->postgresPort};dbname={$this->postgresDb}",
                $this->postgresUser,
                $this->postgresPassword,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,        // Errores como excepciones
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,   // Arrays asociativos por defecto
                ]
            );
        } catch (PDOException $e) {
            die('Error de conexión con la base de datos: ' . $e->getMessage());
        }
    }

    /**
     * Devuelve una variable de entorno o un valor por defecto.
     * Dotenv la guarda en $_ENV; Docker (env_file) la deja en getenv().
     */
    private function env(string $key, string $default): string
    {
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return $_ENV[$key];
        }
        $value = getenv($key);
        return ($value !== false && $value !== '') ? $value : $default;
    }

    public static function getInstance(): Config
    {
        if (!isset(self::$instance)) {
            self::$instance = new Config();
        }
        return self::$instance;
    }

    // Impide clonar el Singleton
    private function __clone()
    {
    }

    // Métodos mágicos get y set
    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }
}
