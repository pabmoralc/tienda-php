<?php
/**
 * login.php - Inicio de sesión.
 * GET  -> muestra el formulario.
 * POST -> valida los campos, autentica con UsersService y, si es correcto,
 *         inicia la sesión y redirige al listado.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/config/Config.php';
require_once __DIR__ . '/services/SessionService.php';
require_once __DIR__ . '/services/UsersService.php';

use config\Config;
use services\SessionService;
use services\UsersService;

$session = SessionService::getInstance();

// Si ya ha iniciado sesión, no tiene sentido volver a mostrar el login
if ($session->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = null;
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Filtrar
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';   // La contraseña no se recorta: los espacios cuentan

    // 2. Validar que no falta nada
    if ($username === '' || $password === '') {
        $error = 'Introduce el usuario y la contraseña.';
    } else {
        // 3. Autenticar: authenticate() lanza una excepción si los datos no son correctos
        try {
            $usersService = new UsersService(Config::getInstance()->db);
            $user = $usersService->authenticate($username, $password);

            $session->login($user);
            $session->setMensaje('success', "¡Bienvenido, {$user->nombre}!");
            header('Location: index.php');
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$titulo = 'Login';
require_once __DIR__ . '/header.php';
?>

<div class="row justify-content-center">
    <div class="col-sm-10 col-md-6 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h3 text-center mb-4">Iniciar sesión</h1>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="post" action="login.php" novalidate>
                    <div class="mb-3">
                        <label for="username" class="form-label">Usuario</label>
                        <input type="text" id="username" name="username" class="form-control"
                               value="<?= htmlspecialchars($username) ?>" autocomplete="username" required autofocus>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" id="password" name="password" class="form-control"
                               autocomplete="current-password" required>
                    </div>
                    <button type="submit" class="btn btn-dark w-100">Entrar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
