<?php
/**
 * create.php - Alta de un producto nuevo.
 * GET  -> muestra el formulario vacío.
 * POST -> valida los datos; si hay errores, vuelve a mostrar el formulario con los errores;
 *         si todo es correcto, guarda el producto y redirige al listado.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/config/Config.php';
require_once __DIR__ . '/services/SessionService.php';
require_once __DIR__ . '/services/ProductosService.php';
require_once __DIR__ . '/services/CategoriasService.php';

use config\Config;
use models\Producto;
use services\CategoriasService;
use services\ProductosService;
use services\SessionService;

$session = SessionService::getInstance();
// Fase 4: aquí se comprobará que el usuario es administrador.

$config = Config::getInstance();
$productosService = new ProductosService($config->db);
$categorias = (new CategoriasService($config->db))->findAll();

$errores = [];
// Valores del formulario (vacíos la primera vez; con lo escrito si hay errores)
$datos = ['marca' => '', 'modelo' => '', 'descripcion' => '', 'precio' => '', 'stock' => '', 'categoria' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Filtrar: recoger cada campo y quitar espacios sobrantes
    foreach ($datos as $campo => $valor) {
        $datos[$campo] = trim($_POST[$campo] ?? '');
    }

    // 2. Validar
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

    // Se acepta coma o punto decimal ("12,50" o "12.50")
    $precio = filter_var(str_replace(',', '.', $datos['precio']), FILTER_VALIDATE_FLOAT);
    if ($precio === false || $precio <= 0) {
        $errores['precio'] = 'El precio debe ser un número mayor que 0.';
    }

    $stock = filter_var($datos['stock'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    if ($stock === false) {
        $errores['stock'] = 'El stock debe ser un número entero igual o mayor que 0.';
    }

    // La categoría tiene que ser una de las que existen en la BD
    $idsCategorias = array_map(fn($categoria) => $categoria->id, $categorias);
    if (!in_array($datos['categoria'], $idsCategorias, true)) {
        $errores['categoria'] = 'Selecciona una categoría válida.';
    }

    // 3. Si no hay errores, guardar y redirigir
    if (empty($errores)) {
        $producto = new Producto();
        $producto->marca = $datos['marca'];
        $producto->modelo = $datos['modelo'];
        $producto->descripcion = $datos['descripcion'];
        $producto->precio = $precio;
        $producto->stock = $stock;
        $producto->categoriaId = $datos['categoria'];

        try {
            $id = $productosService->save($producto);
            $session->setMensaje('success', "Producto «{$producto->modelo}» creado correctamente (ID $id).");
            header('Location: index.php');   // Patrón PRG: Post -> Redirect -> Get
            exit;
        } catch (PDOException $e) {
            $errores['general'] = 'No se ha podido guardar el producto. Inténtalo de nuevo.';
        }
    }
}

$titulo = 'Nuevo producto';
require_once __DIR__ . '/header.php';
?>

<h1 class="mb-4">Nuevo producto</h1>

<?php if (isset($errores['general'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($errores['general']) ?></div>
<?php endif; ?>

<!-- novalidate: desactiva la validación del navegador para que se vea la de PHP -->
<form method="post" action="create.php" class="row g-3" novalidate>
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

    <div class="col-12">
        <p class="text-muted small mb-0">Se asignará una imagen por defecto; podrás cambiarla después.</p>
    </div>

    <div class="col-12 d-flex gap-2">
        <button type="submit" class="btn btn-success">Guardar</button>
        <a href="index.php" class="btn btn-secondary">Volver</a>
    </div>
</form>

<?php require_once __DIR__ . '/footer.php'; ?>
