<?php

namespace services;

use models\Producto;
use PDO;
use Ramsey\Uuid\Uuid;

require_once __DIR__ . '/../models/Producto.php';

/**
 * Class ProductosService
 * Operaciones CRUD sobre la tabla "productos".
 * Todas las consultas usan sentencias preparadas para evitar inyección SQL.
 */
class ProductosService
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Devuelve todos los productos con el nombre de su categoría (JOIN).
     * Si llega un término de búsqueda, filtra por marca o modelo.
     * @return Producto[]
     */
    public function findAllWithCategoryName($searchTerm = null): array
    {
        $sql = "SELECT p.*, c.nombre AS categoria_nombre
                FROM productos p
                LEFT JOIN categorias c ON p.categoria_id = c.id";

        if ($searchTerm) {
            // ILIKE = LIKE sin distinguir mayúsculas/minúsculas (PostgreSQL)
            $sql .= " WHERE p.marca ILIKE :term1 OR p.modelo ILIKE :term2";
        }
        $sql .= " ORDER BY p.id ASC";

        $stmt = $this->pdo->prepare($sql);
        if ($searchTerm) {
            $term = '%' . $searchTerm . '%';   // % = cualquier texto antes o después
            $stmt->bindValue(':term1', $term, PDO::PARAM_STR);
            $stmt->bindValue(':term2', $term, PDO::PARAM_STR);
        }
        $stmt->execute();

        $productos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $productos[] = $this->mapRow($row);
        }
        return $productos;
    }

    /**
     * Busca un producto por su id (con el nombre de su categoría).
     * Devuelve null si no existe.
     */
    public function findById($id): ?Producto
    {
        $stmt = $this->pdo->prepare(
            "SELECT p.*, c.nombre AS categoria_nombre
             FROM productos p
             LEFT JOIN categorias c ON p.categoria_id = c.id
             WHERE p.id = :id"
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapRow($row) : null;
    }

    /**
     * Inserta un producto nuevo. Genera su UUID y pone las fechas.
     * Devuelve el id asignado por la secuencia.
     */
    public function save(Producto $producto): int
    {
        $producto->uuid = Uuid::uuid4()->toString();
        $producto->imagen = $producto->imagen ?: Producto::$IMAGEN_DEFAULT;

        $stmt = $this->pdo->prepare(
            "INSERT INTO productos (uuid, marca, modelo, descripcion, precio, stock, imagen,
                                    categoria_id, created_at, updated_at, is_deleted)
             VALUES (:uuid, :marca, :modelo, :descripcion, :precio, :stock, :imagen,
                     :categoria_id, NOW(), NOW(), false)
             RETURNING id"
        );
        $stmt->bindValue(':uuid', $producto->uuid, PDO::PARAM_STR);
        $stmt->bindValue(':marca', $producto->marca, PDO::PARAM_STR);
        $stmt->bindValue(':modelo', $producto->modelo, PDO::PARAM_STR);
        $stmt->bindValue(':descripcion', $producto->descripcion, PDO::PARAM_STR);
        $stmt->bindValue(':precio', $producto->precio);                 // float: sin tipo
        $stmt->bindValue(':stock', $producto->stock, PDO::PARAM_INT);
        $stmt->bindValue(':imagen', $producto->imagen, PDO::PARAM_STR);
        $stmt->bindValue(':categoria_id', $producto->categoriaId, PDO::PARAM_STR);
        $stmt->execute();

        // RETURNING id devuelve el id que ha generado la secuencia
        $producto->id = (int)$stmt->fetchColumn();
        return $producto->id;
    }

    /**
     * Actualiza todos los campos editables de un producto existente.
     * Devuelve true si se ha modificado alguna fila.
     */
    public function update(Producto $producto): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE productos
             SET marca = :marca, modelo = :modelo, descripcion = :descripcion,
                 precio = :precio, stock = :stock, imagen = :imagen,
                 categoria_id = :categoria_id, updated_at = NOW()
             WHERE id = :id"
        );
        $stmt->bindValue(':marca', $producto->marca, PDO::PARAM_STR);
        $stmt->bindValue(':modelo', $producto->modelo, PDO::PARAM_STR);
        $stmt->bindValue(':descripcion', $producto->descripcion, PDO::PARAM_STR);
        $stmt->bindValue(':precio', $producto->precio);
        $stmt->bindValue(':stock', $producto->stock, PDO::PARAM_INT);
        $stmt->bindValue(':imagen', $producto->imagen, PDO::PARAM_STR);
        $stmt->bindValue(':categoria_id', $producto->categoriaId, PDO::PARAM_STR);
        $stmt->bindValue(':id', $producto->id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Borra físicamente un producto. Devuelve true si se ha borrado.
     */
    public function deleteById($id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM productos WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Convierte una fila de la BD (snake_case) en un objeto Producto (camelCase).
     */
    private function mapRow(array $row): Producto
    {
        return new Producto(
            $row['id'],
            $row['uuid'],
            $row['descripcion'],
            $row['imagen'],
            $row['marca'],
            $row['modelo'],
            $row['precio'],
            $row['stock'],
            $row['created_at'],
            $row['updated_at'],
            $row['categoria_id'],
            $row['categoria_nombre'] ?? null,
            $row['is_deleted']
        );
    }
}
