<?php

namespace services;

use Exception;
use models\User;
use PDO;

require_once __DIR__ . '/../models/User.php';

/**
 * Class UsersService
 * Autenticación y consulta de usuarios (tablas "usuarios" y "user_roles").
 */
class UsersService
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Comprueba usuario y contraseña. Devuelve el User si es correcto
     * o lanza una Exception si no lo es.
     */
    public function authenticate($username, $password): User
    {
        $user = $this->findUserByUsername($username);

        if ($user === null || !password_verify($password, $user->password)) {
            // Mismo mensaje en ambos casos: no se revela si el usuario existe
            throw new Exception('Usuario o contraseña incorrectos');
        }
        return $user;
    }

    /**
     * Busca un usuario activo por username e incluye sus roles.
     * Devuelve null si no existe.
     */
    public function findUserByUsername($username): ?User
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM usuarios WHERE username = :username AND is_deleted = false"
        );
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        // Roles del usuario
        $stmtRoles = $this->db->prepare("SELECT roles FROM user_roles WHERE user_id = :user_id");
        $stmtRoles->bindParam(':user_id', $row['id'], PDO::PARAM_INT);
        $stmtRoles->execute();
        $roles = $stmtRoles->fetchAll(PDO::FETCH_COLUMN);

        return new User(
            $row['id'],
            $row['username'],
            $row['password'],
            $row['nombre'],
            $row['apellidos'],
            $row['email'],
            $row['created_at'],
            $row['updated_at'],
            $row['is_deleted'],
            $roles
        );
    }
}
