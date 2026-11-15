<?php
/**
 * update-image.php - Formulario para cambiar la imagen de un producto (update-image.php?id=X).
 * El archivo se envía a update_image_file.php, que lo valida y lo guarda.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/config/Config.php';
require_once __DIR__ . '/services/SessionService.php';
require_once __DIR__ . '/services/ProductosService.php';

use config\Config;
use services\ProductosService;
use services\SessionService;

$session = SessionService::getInstance();
$session->requireAdmin();   // Solo el administrador puede continuar

$config = Config::getInstance();
$productosService = new ProductosService($config->db);

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$producto = $id ? $productosService->findById($id) : null;

if (!$producto) {
    $session->setMensaje('warning', 'El producto cuya imagen quieres cambiar no existe.');
    header('Location: index.php');
    exit;
}

$titulo = 'Cambiar imagen';
require_once __DIR__ . '/header.php';
?>

<h1 class="mb-4">Cambiar imagen</h1>

<div class="row g-4">
    <div class="col-md-4">
        <img src="<?= htmlspecialchars($producto->imagen) ?>" alt="Imagen actual de <?= htmlspecialchars($producto->modelo) ?>"
             class="img-fluid rounded border">
        <p class="text-muted small mt-2 mb-0">Imagen actual</p>
    </div>

    <div class="col-md-8">
        <dl class="row">
            <dt class="col-sm-3">ID</dt>
            <dd class="col-sm-9"><?= $producto->id ?></dd>
            <dt class="col-sm-3">Marca</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($producto->marca) ?></dd>
            <dt class="col-sm-3">Modelo</dt>
            <dd class="col-sm-9"><?= htmlspecialchars($producto->modelo) ?></dd>
        </dl>

        <!-- enctype="multipart/form-data" es obligatorio para poder enviar archivos -->
        <form method="post" action="update_image_file.php" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $producto->id ?>">

            <div class="mb-3">
                <label for="imagen" class="form-label">Nueva imagen (JPG o PNG, máximo 2 MB)</label>
                <input type="file" id="imagen" name="imagen" class="form-control"
                       accept="image/jpeg,image/png" required>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-info">Actualizar imagen</button>
                <a href="index.php" class="btn btn-secondary">Volver</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
