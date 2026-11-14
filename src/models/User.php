<?php

namespace models;

/**
 * Class User
 * Representa una fila de la tabla "usuarios" junto con sus roles (tabla "user_roles").
 */
class User
{
    private $id;
    private $username;
    private $password;   // Hash bcrypt, nunca la contraseña en claro
    private $nombre;
    private $apellidos;
    private $email;
    private $createdAt;
    private $updatedAt;
    private $isDeleted;
    private $roles = []; // Ej: ['USER', 'ADMIN']

    public function __construct(
        $id = null,
        $username = null,
        $password = null,
        $nombre = null,
        $apellidos = null,
        $email = null,
        $createdAt = null,
        $updatedAt = null,
        $isDeleted = false,
        $roles = []
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
        $this->nombre = $nombre;
        $this->apellidos = $apellidos;
        $this->email = $email;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->isDeleted = $isDeleted;
        $this->roles = $roles;
    }

    /**
     * Indica si el usuario tiene el rol ADMIN.
     */
    public function isAdmin(): bool
    {
        return in_array('ADMIN', $this->roles, true);
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
