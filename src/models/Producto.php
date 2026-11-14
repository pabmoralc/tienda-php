<?php

namespace models;

/**
 * Class Producto
 * Representa una fila de la tabla "productos".
 * categoriaNombre no es columna de la tabla: se rellena con un JOIN a categorias.
 */
class Producto
{
    public static $IMAGEN_DEFAULT = 'https://placehold.co/150';

    private $id;
    private $uuid;
    private $descripcion;
    private $imagen;
    private $marca;
    private $modelo;
    private $precio;
    private $stock;
    private $createdAt;
    private $updatedAt;
    private $categoriaId;
    private $categoriaNombre;
    private $isDeleted;

    public function __construct(
        $id = null,
        $uuid = null,
        $descripcion = null,
        $imagen = null,
        $marca = null,
        $modelo = null,
        $precio = 0.0,
        $stock = 0,
        $createdAt = null,
        $updatedAt = null,
        $categoriaId = null,
        $categoriaNombre = null,
        $isDeleted = false
    ) {
        $this->id = $id;
        $this->uuid = $uuid;
        $this->descripcion = $descripcion;
        $this->imagen = $imagen ?? self::$IMAGEN_DEFAULT;
        $this->marca = $marca;
        $this->modelo = $modelo;
        $this->precio = $precio;
        $this->stock = $stock;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->categoriaId = $categoriaId;
        $this->categoriaNombre = $categoriaNombre;
        $this->isDeleted = $isDeleted;
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
