<?php

namespace services;

/**
 * Class SessionService
 * @package services
 * Esta clase se encarga de gestionar la sesión de usuario (patrón Singleton).
 *
 * Hecho: instancia única, lectura del estado de la sesión y mensajes flash.
 * Pendiente (Fase 4): login(), logout(), expiración por inactividad y contador de visitas.
 */
class SessionService
{
    private static $instance;

    private function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Valores iniciales si la sesión es nueva
        $_SESSION['loggedIn'] = $_SESSION['loggedIn'] ?? false;
        $_SESSION['username'] = $_SESSION['username'] ?? null;
        $_SESSION['isAdmin'] = $_SESSION['isAdmin'] ?? false;
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
