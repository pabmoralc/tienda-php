<?php

use config\Config;
use models\Producto;
use PHPUnit\Framework\TestCase;
use services\CategoriasService;
use services\UsersService;

/**
 * Pruebas de la Fase 1: conexión, modelos y servicios base.
 * Requiere la base de datos en marcha. Ejecutar dentro del contenedor:
 *   docker exec -it php_app vendor/bin/phpunit
 */
class Fase1Test extends TestCase
{
    private static PDO $db;

    public static function setUpBeforeClass(): void
    {
        self::$db = Config::getInstance()->db;
    }

    public function testConexionPdo(): void
    {
        $this->assertInstanceOf(PDO::class, self::$db);
        $this->assertEquals(1, self::$db->query('SELECT 1')->fetchColumn());
    }

    public function testConfigEsSingleton(): void
    {
        $this->assertSame(Config::getInstance(), Config::getInstance());
    }

    public function testCategoriasFindAll(): void
    {
        $categorias = (new CategoriasService(self::$db))->findAll();
        $this->assertCount(5, $categorias);
    }

    public function testCategoriasFindByName(): void
    {
        $service = new CategoriasService(self::$db);
        $this->assertEquals('MUJER', $service->findByName('MUJER')->nombre);
        $this->assertFalse($service->findByName('NO_EXISTE'));
    }

    public function testAdminTieneRolAdmin(): void
    {
        $admin = (new UsersService(self::$db))->findUserByUsername('admin');
        $this->assertNotNull($admin);
        $this->assertTrue($admin->isAdmin());
    }

    public function testAuthenticateCorrecto(): void
    {
        $user = (new UsersService(self::$db))->authenticate('user', 'User1234');
        $this->assertEquals('user', $user->username);
        $this->assertFalse($user->isAdmin());
    }

    public function testAuthenticateIncorrectoLanzaExcepcion(): void
    {
        $this->expectException(Exception::class);
        (new UsersService(self::$db))->authenticate('admin', 'mala');
    }

    public function testProductoImagenPorDefecto(): void
    {
        $producto = new Producto();
        $this->assertEquals(Producto::$IMAGEN_DEFAULT, $producto->imagen);
    }
}
