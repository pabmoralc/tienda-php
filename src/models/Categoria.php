<?php

namespace models;

use Ramsey\Uuid\Uuid;

/**
 * Class Categoria
 * Representa una fila de la tabla "categorias".
 */
class Categoria
{
    private $id;
    private $nombre;
    private $createdAt;
    private $updatedAt;
    private $isDeleted;

    public function __construct($id = null, $nombre = null, $createdAt = null, $updatedAt = null, $isDeleted = false)
    {
        $this->id = $id ?? $this->generateUUID();
        $this->nombre = $nombre;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->isDeleted = $isDeleted;
    }

    public function getId()
    {
        return $this->id;
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

    private function generateUUID()
    {
        return Uuid::uuid4()->toString();
    }
}
