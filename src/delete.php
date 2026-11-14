<?php
/**
 * delete.php - Eliminación de un producto (delete.php?id=X).
 * GET  -> pide confirmación.
 * POST -> borra la imagen subida (si la tiene) y el producto, y redirige al listado.
 * Se confirma con POST para que un simple enlace o una recarga no borren nada.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/config/Config.php';
require_once __DIR__ . '/services/SessionService.php';
require_once __DIR__ . '/services/ProductosService.php';

use config\Config;
use services\ProductosService;
use services\SessionService;

$session = SessionService::getInstance();
// Fase 4: aquí se comprobará que el usuario es administrador.

$config = Config::getInstance();
$productosService = new ProductosService($config->db);

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$producto = $id ? $productosService->findById($id) : null;

if (!$producto) {
    $session->setMensaje('warning', 'El producto que intentas eliminar no existe.');
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Si la imagen es una subida por nosotros (está en /uploads), se borra el archivo
    if (str_starts_with($producto->imagen, $config->uploadUrl)) {
        $archivo = $config->uploadPath . basename($producto->imagen);
        if (is_file($archivo)) {
            unlink($archivo);
        }
    }

    // 2. Borrar el producto de la BD
    if ($productosService->deleteById($producto->id)) {
        $session->setMensaje('success', "Producto «{$producto->modelo}» eliminado correctamente.");
    } else {
        $session->setMensaje('danger', 'No se ha podido eliminar el producto.');
    }
    header('Location: index.php');
    exit;
}

$titulo = 'Eliminar producto';
require_once __DIR__ . '/header.php';
?>

<h1 class="mb-4">Eliminar producto</h1>

<div class="alert alert-danger">
    ¿Seguro que quieres eliminar <strong><?= htmlspecialchars($producto->marca . ' – ' . $producto->modelo) ?></strong>
    (ID <?= $producto->id ?>)? Esta acción no se puede deshacer.
</div>

<form method="post" action="delete.php?id=<?= $producto->id ?>" class="d-flex gap-2">
    <button type="submit" class="btn btn-danger">Sí, eliminar</button>
    <a href="index.php" class="btn btn-secondary">Cancelar</a>
</form>

<?php require_once __DIR__ . '/footer.php'; ?>
