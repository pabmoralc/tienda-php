<?php

use config\Config;
use models\Producto;
use PHPUnit\Framework\TestCase;
use services\ProductosService;

/**
 * Pruebas de las fases 2 y 3: CRUD completo de ProductosService.
 * Crea un producto de prueba, lo lee, lo actualiza y lo borra.
 */
class ProductosServiceTest extends TestCase
{
    private const CATEGORIA_MUJER = 'd69cf3db-b77d-4181-b3cd-5ca8107fb6a9';
    private const CATEGORIA_HOMBRE = '6dbcbf5e-8e1c-47cc-8578-7b0a33ebc154';

    private static ProductosService $service;

    public static function setUpBeforeClass(): void
    {
        self::$service = new ProductosService(Config::getInstance()->db);
    }

    public function testListadoIncluyeNombreDeCategoria(): void
    {
        $productos = self::$service->findAllWithCategoryName();
        $this->assertNotEmpty($productos);
        $this->assertNotNull($productos[0]->categoriaNombre);
    }

    public function testBusquedaPorMarcaSinDistinguirMayusculas(): void
    {
        $productos = self::$service->findAllWithCategoryName('dior');
        $this->assertNotEmpty($productos);
        foreach ($productos as $producto) {
            $this->assertStringContainsStringIgnoringCase('dior', $producto->marca . ' ' . $producto->modelo);
        }
    }

    public function testFindByIdInexistenteDevuelveNull(): void
    {
        $this->assertNull(self::$service->findById(999999));
    }

    public function testCrudCompleto(): void
    {
        // CREATE
        $producto = new Producto();
        $producto->marca = 'Marca Test';
        $producto->modelo = 'Modelo Test 50 ml';
        $producto->descripcion = 'Producto creado por PHPUnit';
        $producto->precio = 10.5;
        $producto->stock = 3;
        $producto->categoriaId = self::CATEGORIA_MUJER;

        $id = self::$service->save($producto);
        $this->assertGreaterThan(0, $id);

        // READ
        $leido = self::$service->findById($id);
        $this->assertSame('Marca Test', $leido->marca);
        $this->assertSame('MUJER', $leido->categoriaNombre);
        $this->assertSame(Producto::$IMAGEN_DEFAULT, $leido->imagen);
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $leido->uuid);

        // UPDATE
        $leido->precio = 20;
        $leido->categoriaId = self::CATEGORIA_HOMBRE;
        $this->assertTrue(self::$service->update($leido));
        $actualizado = self::$service->findById($id);
        $this->assertEquals(20, $actualizado->precio);
        $this->assertSame('HOMBRE', $actualizado->categoriaNombre);

        // DELETE
        $this->assertTrue(self::$service->deleteById($id));
        $this->assertNull(self::$service->findById($id));
        $this->assertFalse(self::$service->deleteById($id));
    }
}
