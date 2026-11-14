<?php

namespace services;

use models\Categoria;
use PDO;

require_once __DIR__ . '/../models/Categoria.php';

/**
 * Class CategoriasService
 * Acceso a la tabla "categorias".
 */
class CategoriasService
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Devuelve todas las categorías como array de objetos Categoria.
     */
    public function findAll(): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM categorias ORDER BY id ASC");
        $stmt->execute();

        $categorias = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $categorias[] = $this->mapRow($row);
        }
        return $categorias;
    }

    /**
     * Busca una categoría por su nombre exacto. Devuelve false si no existe.
     */
    public function findByName($name)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM categorias WHERE nombre = :nombre");
        $stmt->execute(['nombre' => $name]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return false;
        }
        return $this->mapRow($row);
    }

    /**
     * Convierte una fila de la BD en un objeto Categoria.
     */
    private function mapRow(array $row): Categoria
    {
        return new Categoria(
            $row['id'],
            $row['nombre'],
            $row['created_at'],
            $row['updated_at'],
            $row['is_deleted']
        );
    }
}
