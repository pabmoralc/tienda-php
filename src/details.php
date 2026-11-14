<?php
/**
 * details.php - Ficha de un producto (details.php?id=X).
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/config/Config.php';
require_once __DIR__ . '/services/SessionService.php';
require_once __DIR__ . '/services/ProductosService.php';

use config\Config;
use services\ProductosService;
use services\SessionService;

$session = SessionService::getInstance();
$config = Config::getInstance();
$productosService = new ProductosService($config->db);

// El id tiene que ser un entero positivo; si no, o si no existe, vuelta al listado
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$producto = $id ? $productosService->findById($id) : null;

if (!$producto) {
    $session->setMensaje('warning', 'El producto solicitado no existe.');
    header('Location: index.php');
    exit;
}

$titulo = $producto->modelo;
require_once __DIR__ . '/header.php';
?>

<h1 class="mb-4">
    <?= htmlspecialchars($producto->marca) ?>
    <small class="text-muted"><?= htmlspecialchars($producto->modelo) ?></small>
</h1>

<div class="row g-4">
    <div class="col-md-4">
        <img src="<?= htmlspecialchars($producto->imagen) ?>" alt="<?= htmlspecialchars($producto->modelo) ?>"
             class="img-fluid rounded border">
    </div>
    <div class="col-md-8">
        <!-- Lista de definición: <dt> = nombre del dato, <dd> = valor -->
        <dl class="row">
            <dt class="col-sm-3">ID</dt>
            <dd class="col-sm-9"><?= $producto->id ?></dd>

            <dt class="col-sm-3">UUID</dt>
            <dd class="col-sm-9"><code><?= htmlspecialchars($producto->uuid) ?></code></dd>

            <dt class="col-sm-3">Marca</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($producto->marca) ?></dd>

            <dt class="col-sm-3">Modelo</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($producto->modelo) ?></dd>

            <dt class="col-sm-3">Descripción</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($producto->descripcion ?: '—') ?></dd>

            <dt class="col-sm-3">Categoría</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($producto->categoriaNombre ?? 'Sin categoría') ?></dd>

            <dt class="col-sm-3">Precio</dt>
            <dd class="col-sm-9"><?= number_format($producto->precio, 2, ',', '.') ?> €</dd>

            <dt class="col-sm-3">Stock</dt>
            <dd class="col-sm-9"><?= $producto->stock ?> unidades</dd>

            <dt class="col-sm-3">Creado</dt>
            <dd class="col-sm-9"><?= date('d/m/Y H:i', strtotime($producto->createdAt)) ?></dd>

            <dt class="col-sm-3">Actualizado</dt>
            <dd class="col-sm-9"><?= date('d/m/Y H:i', strtotime($producto->updatedAt)) ?></dd>
        </dl>

        <a href="index.php" class="btn btn-secondary">Volver al listado</a>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
