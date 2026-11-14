<?php
/**
 * header.php - Cabecera común de todas las páginas.
 * Abre el HTML, pinta la barra de navegación y muestra el mensaje flash pendiente.
 * Antes de incluirlo, cada página puede definir $titulo.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/services/SessionService.php';

use services\SessionService;

$session = SessionService::getInstance();
$username = $session->getUsername();
$mensaje = $session->getMensaje();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo ?? 'Tienda') ?> | Esencia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">Esencia · Perfumería</a>

        <!-- Botón "hamburguesa" para móviles -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu"
                aria-controls="menu" aria-expanded="false" aria-label="Abrir menú">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Productos</a></li>
                <li class="nav-item"><a class="nav-link" href="create.php">Nuevo producto</a></li>
                <li class="nav-item">
                    <?php if ($session->isLoggedIn()): ?>
                        <a class="nav-link" href="logout.php">Logout</a>
                    <?php else: ?>
                        <a class="nav-link" href="login.php">Login</a>
                    <?php endif; ?>
                </li>
            </ul>
            <span class="navbar-text">👤 <?= htmlspecialchars($username) ?></span>
        </div>
    </div>
</nav>

<main class="container flex-grow-1">
    <?php if ($mensaje): ?>
        <div class="alert alert-<?= htmlspecialchars($mensaje['tipo']) ?> alert-dismissible fade show">
            <?= htmlspecialchars($mensaje['texto']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>
