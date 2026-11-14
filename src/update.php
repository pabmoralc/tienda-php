<?php
/**
 * update.php - Edición de un producto existente (update.php?id=X).
 * GET  -> formulario relleno con los datos actuales.
 * POST -> valida; si es correcto, actualiza y redirige al listado.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/config/Config.php';
require_once __DIR__ . '/services/SessionService.php';
require_once __DIR__ . '/services/ProductosService.php';
require_once __DIR__ . '/services/CategoriasService.php';

use config\Config;
use services\CategoriasService;
use services\ProductosService;
use services\SessionService;

$session = SessionService::getInstance();
// Fase 4: aquí se comprobará que el usuario es administrador.

$config = Config::getInstance();
$productosService = new ProductosService($config->db);
$categorias = (new CategoriasService($config->db))->findAll();

// 1. El id llega por la URL y tiene que ser un entero positivo
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$producto = $id ? $productosService->findById($id) : null;

if (!$producto) {
    $session->setMensaje('warning', 'El producto que intentas editar no existe.');
    header('Location: index.php');
    exit;
}

$errores = [];
// La primera vez, el formulario se rellena con los datos actuales del producto
$datos = [
    'marca' => $producto->marca,
    'modelo' => $producto->modelo,
    'descripcion' => $producto->descripcion ?? '',
    'precio' => $producto->precio,
    'stock' => $producto->stock,
    'categoria' => $producto->categoriaId ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2. Filtrar
    foreach ($datos as $campo => $valor) {
        $datos[$campo] = trim($_POST[$campo] ?? '');
    }

    // 3. Validar (mismas reglas que en create.php)
    if ($datos['marca'] === '') {
        $errores['marca'] = 'La marca es obligatoria.';
    } elseif (mb_strlen($datos['marca']) > 255) {
        $errores['marca'] = 'La marca no puede superar los 255 caracteres.';
    }

    if ($datos['modelo'] === '') {
        $errores['modelo'] = 'El modelo es obligatorio.';
    } elseif (mb_strlen($datos['modelo']) > 255) {
        $errores['modelo'] = 'El modelo no puede superar los 255 caracteres.';
    }

    if (mb_strlen($datos['descripcion']) > 255) {
        $errores['descripcion'] = 'La descripción no puede superar los 255 caracteres.';
    }

    $precio = filter_var(str_replace(',', '.', $datos['precio']), FILTER_VALIDATE_FLOAT);
    if ($precio === false || $precio <= 0) {
        $errores['precio'] = 'El precio debe ser un número mayor que 0.';
    }

    $stock = filter_var($datos['stock'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    if ($stock === false) {
        $errores['stock'] = 'El stock debe ser un número entero igual o mayor que 0.';
    }

    $idsCategorias = array_map(fn($categoria) => $categoria->id, $categorias);
    if (!in_array($datos['categoria'], $idsCategorias, true)) {
        $errores['categoria'] = 'Selecciona una categoría válida.';
    }

    // 4. Actualizar el objeto y guardarlo (la imagen no se toca aquí)
    if (empty($errores)) {
        $producto->marca = $datos['marca'];
        $producto->modelo = $datos['modelo'];
        $producto->descripcion = $datos['descripcion'];
        $producto->precio = $precio;
        $producto->stock = $stock;
        $producto->categoriaId = $datos['categoria'];

        try {
            $productosService->update($producto);
            $session->setMensaje('success', "Producto «{$producto->modelo}» actualizado correctamente.");
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $errores['general'] = 'No se ha podido actualizar el producto. Inténtalo de nuevo.';
        }
    }
}

$titulo = 'Editar producto';
require_once __DIR__ . '/header.php';
?>

<h1 class="mb-4">Editar producto #<?= $producto->id ?></h1>

<?php if (isset($errores['general'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($errores['general']) ?></div>
<?php endif; ?>

<form method="post" action="update.php?id=<?= $producto->id ?>" class="row g-3" novalidate>
    <div class="col-md-6">
        <label for="marca" class="form-label">Marca</label>
        <input type="text" id="marca" name="marca" maxlength="255"
               class="form-control <?= isset($errores['marca']) ? 'is-invalid' : '' ?>"
               value="<?= htmlspecialchars($datos['marca']) ?>" required>
        <div class="invalid-feedback"><?= $errores['marca'] ?? '' ?></div>
    </div>

    <div class="col-md-6">
        <label for="modelo" class="form-label">Modelo</label>
        <input type="text" id="modelo" name="modelo" maxlength="255"
               class="form-control <?= isset($errores['modelo']) ? 'is-invalid' : '' ?>"
               value="<?= htmlspecialchars($datos['modelo']) ?>" required>
        <div class="invalid-feedback"><?= $errores['modelo'] ?? '' ?></div>
    </div>

    <div class="col-12">
        <label for="descripcion" class="form-label">Descripción</label>
        <textarea id="descripcion" name="descripcion" rows="3" maxlength="255"
                  class="form-control <?= isset($errores['descripcion']) ? 'is-invalid' : '' ?>"><?= htmlspecialchars($datos['descripcion']) ?></textarea>
        <div class="invalid-feedback"><?= $errores['descripcion'] ?? '' ?></div>
    </div>

    <div class="col-md-4">
        <label for="precio" class="form-label">Precio (€)</label>
        <input type="text" id="precio" name="precio" inputmode="decimal"
               class="form-control <?= isset($errores['precio']) ? 'is-invalid' : '' ?>"
               value="<?= htmlspecialchars($datos['precio']) ?>" required>
        <div class="invalid-feedback"><?= $errores['precio'] ?? '' ?></div>
    </div>

    <div class="col-md-4">
        <label for="stock" class="form-label">Stock</label>
        <input type="number" id="stock" name="stock" min="0" step="1"
               class="form-control <?= isset($errores['stock']) ? 'is-invalid' : '' ?>"
               value="<?= htmlspecialchars($datos['stock']) ?>" required>
        <div class="invalid-feedback"><?= $errores['stock'] ?? '' ?></div>
    </div>

    <div class="col-md-4">
        <label for="categoria" class="form-label">Categoría</label>
        <select id="categoria" name="categoria"
                class="form-select <?= isset($errores['categoria']) ? 'is-invalid' : '' ?>" required>
            <option value="">-- Selecciona --</option>
            <?php foreach ($categorias as $categoria): ?>
                <option value="<?= htmlspecialchars($categoria->id) ?>"
                    <?= $datos['categoria'] === $categoria->id ? 'selected' : '' ?>>
                    <?= htmlspecialchars($categoria->nombre) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <div class="invalid-feedback"><?= $errores['categoria'] ?? '' ?></div>
    </div>

    <div class="col-md-8">
        <label for="imagen" class="form-label">Imagen (solo lectura)</label>
        <input type="text" id="imagen" class="form-control" value="<?= htmlspecialchars($producto->imagen) ?>" readonly>
    </div>
    <div class="col-md-4 d-flex align-items-end">
        <img src="<?= htmlspecialchars($producto->imagen) ?>" alt="Imagen actual" width="80" height="80"
             class="rounded object-fit-cover">
    </div>

    <div class="col-12 d-flex gap-2">
        <button type="submit" class="btn btn-warning">Guardar cambios</button>
        <a href="index.php" class="btn btn-secondary">Volver</a>
    </div>
</form>

<?php require_once __DIR__ . '/footer.php'; ?>
