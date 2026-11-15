<?php
/**
 * update_image_file.php - Procesa la subida de la imagen de un producto.
 * No muestra HTML: valida el archivo, lo guarda en /uploads con el nombre <uuid>.<ext>,
 * actualiza la ruta en la BD y redirige.
 *   - Todo correcto   -> vuelve a update-image.php?id=X con mensaje de éxito.
 *   - Cualquier error -> vuelve a index.php con mensaje de error.
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

/**
 * Guarda el mensaje de error y vuelve al listado.
 */
function volverConError(SessionService $session, string $texto): void
{
    $session->setMensaje('danger', $texto);
    header('Location: index.php');
    exit;
}

// Tipos permitidos: MIME real del archivo => extensiones aceptadas
const TIPOS_PERMITIDOS = [
    'image/jpeg' => ['jpg', 'jpeg'],
    'image/png' => ['png'],
];
const TAMANO_MAXIMO = 2 * 1024 * 1024;   // 2 MB en bytes

// 1. Solo se trabaja con POST y con un archivo enviado
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['imagen'])) {
    volverConError($session, 'No se ha recibido ninguna imagen.');
}

$config = Config::getInstance();
$productosService = new ProductosService($config->db);

// 2. El producto tiene que existir
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$producto = $id ? $productosService->findById($id) : null;
if (!$producto) {
    volverConError($session, 'El producto no existe.');
}

// 3. La subida tiene que haber ido bien
$archivo = $_FILES['imagen'];
if ($archivo['error'] === UPLOAD_ERR_NO_FILE) {
    volverConError($session, 'No has seleccionado ninguna imagen.');
}
// UPLOAD_ERR_INI_SIZE: el archivo supera upload_max_filesize de php.ini (2 MB por defecto)
if ($archivo['error'] === UPLOAD_ERR_INI_SIZE || $archivo['size'] > TAMANO_MAXIMO) {
    volverConError($session, 'La imagen no puede superar los 2 MB.');
}
if ($archivo['error'] !== UPLOAD_ERR_OK) {
    volverConError($session, 'Error al subir el archivo (código ' . $archivo['error'] . ').');
}

// 4. Comprobar el tipo por MIME y por extensión.
//    El MIME se lee del contenido real del archivo (finfo), no de $archivo['type'],
//    que lo envía el navegador y se puede falsificar.
$mime = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
$extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

if (!isset(TIPOS_PERMITIDOS[$mime]) || !in_array($extension, TIPOS_PERMITIDOS[$mime], true)) {
    volverConError($session, 'Solo se permiten imágenes JPG o PNG.');
}

// 5. Nombre nuevo a partir del uuid del producto: siempre único y sin caracteres raros
$extension = ($extension === 'jpeg') ? 'jpg' : $extension;
$nombreArchivo = $producto->uuid . '.' . $extension;
$destino = $config->uploadPath . $nombreArchivo;

// Si la imagen anterior era una subida con otra extensión (p. ej. .png -> .jpg), se borra
if (str_starts_with($producto->imagen, $config->uploadUrl)) {
    $anterior = $config->uploadPath . basename(parse_url($producto->imagen, PHP_URL_PATH));
    if ($anterior !== $destino && is_file($anterior)) {
        unlink($anterior);
    }
}

// 6. Mover el archivo de la carpeta temporal a /uploads
if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
    volverConError($session, 'No se ha podido guardar la imagen en el servidor.');
}

// 7. Guardar la nueva URL en la BD.
//    ?v=... cambia en cada subida para que el navegador no muestre la imagen antigua de su caché.
$producto->imagen = $config->uploadUrl . $nombreArchivo . '?v=' . time();
$productosService->update($producto);

$session->setMensaje('success', 'Imagen actualizada correctamente.');
header('Location: update-image.php?id=' . $producto->id);
exit;
