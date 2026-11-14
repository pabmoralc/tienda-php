<?php
/**
 * index.php - Listado de productos con buscador por marca o modelo.
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

// Término de búsqueda (?search=...). trim() quita espacios sobrantes.
$searchTerm = trim($_GET['search'] ?? '');
$productos = $productosService->findAllWithCategoryName($searchTerm);

$titulo = 'Productos';
require_once __DIR__ . '/header.php';
?>

<h1 class="mb-1">Catálogo de perfumes</h1>
<p class="text-muted">Bienvenido, <?= htmlspecialchars($session->getUsername()) ?>.</p>

<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <!-- method="get": la búsqueda queda en la URL y se puede compartir o recargar -->
    <form class="d-flex gap-2" method="get" action="index.php">
        <input class="form-control" type="search" name="search" placeholder="Buscar por marca o modelo"
               value="<?= htmlspecialchars($searchTerm) ?>">
        <button class="btn btn-outline-primary" type="submit">Buscar</button>
        <?php if ($searchTerm !== ''): ?>
            <a class="btn btn-outline-secondary" href="index.php">Limpiar</a>
        <?php endif; ?>
    </form>
    <a class="btn btn-success" href="create.php">+ Nuevo producto</a>
</div>

<?php if (empty($productos)): ?>
    <div class="alert alert-info">No se han encontrado productos.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Imagen</th>
                <th>Marca</th>
                <th>Modelo</th>
                <th>Categoría</th>
                <th class="text-end">Precio</th>
                <th class="text-end">Stock</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($productos as $producto): ?>
                <tr>
                    <td><?= $producto->id ?></td>
                    <td>
                        <img src="<?= htmlspecialchars($producto->imagen) ?>" alt="<?= htmlspecialchars($producto->modelo) ?>"
                             width="60" height="60" class="rounded object-fit-cover">
                    </td>
                    <td><?= htmlspecialchars($producto->marca) ?></td>
                    <td><?= htmlspecialchars($producto->modelo) ?></td>
                    <td><?= htmlspecialchars($producto->categoriaNombre ?? 'Sin categoría') ?></td>
                    <td class="text-end"><?= number_format($producto->precio, 2, ',', '.') ?> €</td>
                    <td class="text-end"><?= $producto->stock ?></td>
                    <td class="text-nowrap">
                        <a class="btn btn-sm btn-primary" href="details.php?id=<?= $producto->id ?>">Detalles</a>
                        <a class="btn btn-sm btn-warning" href="update.php?id=<?= $producto->id ?>">Editar</a>
                        <a class="btn btn-sm btn-danger" href="delete.php?id=<?= $producto->id ?>">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>
