<?php
/**
 * logout.php - Cierra la sesión del usuario y vuelve al listado.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/services/SessionService.php';

use services\SessionService;

$session = SessionService::getInstance();
$session->logout();
$session->setMensaje('info', 'Has cerrado sesión correctamente.');

header('Location: index.php');
exit;
