<?php

namespace services;

use models\User;

/**
 * Class SessionService
 * @package services
 * Esta clase se encarga de gestionar la sesión de usuario (patrón Singleton):
 * login, logout, roles, caducidad por inactividad, contador de visitas y mensajes flash.
 */
class SessionService
{
    private static $instance;

    // La sesión caduca tras 1 hora (3600 s) sin actividad
    private $expireAfterSeconds = 3600;

    private function __construct()
    {
        // Hora española para date() (el contenedor de PHP usa UTC por defecto)
        date_default_timezone_set('Europe/Madrid');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->inicializar();

        // Si la sesión ha caducado, se cierra y se avisa al usuario
        if ($this->isSessionExpired()) {
            $this->logout();
            $this->setMensaje('warning', 'Tu sesión ha caducado por inactividad. Vuelve a iniciar sesión.');
        }

        $_SESSION['lastActivity'] = time();   // Momento de esta petición
        $_SESSION['visits']++;                // Cada página cargada cuenta como una visita
    }

    public static function getInstance(): SessionService
    {
        if (!isset(self::$instance)) {
            self::$instance = new SessionService();
        }
        return self::$instance;
    }

    // Impide clonar el Singleton
    private function __clone()
    {
    }

    /**
     * Da valores por defecto a las variables de sesión que aún no existan.
     */
    private function inicializar(): void
    {
        $_SESSION['loggedIn'] = $_SESSION['loggedIn'] ?? false;
        $_SESSION['username'] = $_SESSION['username'] ?? null;
        $_SESSION['isAdmin'] = $_SESSION['isAdmin'] ?? false;
        $_SESSION['lastLogin'] = $_SESSION['lastLogin'] ?? null;
        $_SESSION['visits'] = $_SESSION['visits'] ?? 0;
        $_SESSION['lastActivity'] = $_SESSION['lastActivity'] ?? time();
    }

    /**
     * Inicia la sesión de un usuario ya autenticado.
     */
    public function login(User $user): void
    {
        // Nuevo identificador de sesión: evita el ataque de fijación de sesión
        session_regenerate_id(true);

        $_SESSION['loggedIn'] = true;
        $_SESSION['username'] = $user->username;
        $_SESSION['isAdmin'] = $user->isAdmin();
        $_SESSION['lastLogin'] = date('d/m/Y H:i:s');
        $_SESSION['visits'] = 1;
        $_SESSION['lastActivity'] = time();
    }

    /**
     * Cierra la sesión: borra todos sus datos y genera un identificador nuevo.
     * La sesión (vacía) sigue abierta para poder mostrar el mensaje de despedida.
     */
    public function logout(): void
    {
        $_SESSION = [];
        session_regenerate_id(true);   // true = borra también la sesión antigua del servidor
        $this->inicializar();
    }

    /**
     * Una sesión iniciada está caducada si lleva más de $expireAfterSeconds sin actividad.
     */
    public function isSessionExpired(): bool
    {
        return $this->isLoggedIn()
            && (time() - $_SESSION['lastActivity']) > $this->expireAfterSeconds;
    }

    /**
     * Si el usuario no es administrador, avisa y lo devuelve al listado.
     * Se llama al principio de las páginas que modifican productos.
     */
    public function requireAdmin(): void
    {
        if (!$this->isAdmin()) {
            $texto = $this->isLoggedIn()
                ? 'No tienes permisos para realizar esta acción.'
                : 'Debes iniciar sesión como administrador para realizar esta acción.';
            $this->setMensaje('danger', $texto);
            header('Location: index.php');
            exit;
        }
    }

    public function isLoggedIn(): bool
    {
        return $_SESSION['loggedIn'] === true;
    }

    public function isAdmin(): bool
    {
        return $this->isLoggedIn() && $_SESSION['isAdmin'] === true;
    }

    /**
     * Nombre del usuario conectado o "Invitado" si no hay sesión.
     */
    public function getUsername(): string
    {
        return $this->isLoggedIn() ? $_SESSION['username'] : 'Invitado';
    }

    public function getVisits(): int
    {
        return $_SESSION['visits'];
    }

    public function getLastLogin(): ?string
    {
        return $_SESSION['lastLogin'];
    }

    /**
     * Guarda un mensaje para mostrarlo en la siguiente página (mensaje "flash").
     * $tipo es una clase de alerta de Bootstrap: success, danger, warning, info.
     */
    public function setMensaje(string $tipo, string $texto): void
    {
        $_SESSION['mensaje'] = ['tipo' => $tipo, 'texto' => $texto];
    }

    /**
     * Devuelve el mensaje pendiente (o null) y lo borra para que solo se vea una vez.
     */
    public function getMensaje(): ?array
    {
        $mensaje = $_SESSION['mensaje'] ?? null;
        unset($_SESSION['mensaje']);
        return $mensaje;
    }
}
