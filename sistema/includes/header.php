<?php
require_once __DIR__ . '/functions.php';
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle ?? 'Assistência Técnica') ?></title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
  <a class="brand" href="index.php">Assistência Técnica</a>
  <?php if (!empty($_SESSION['usuario_id'])): ?>
  <nav><a href="index.php">Início</a><a href="clientes.php">Clientes</a><a href="equipamentos.php">Equipamentos</a><a href="ordens.php">Ordens de serviço</a><a href="logout.php">Sair</a></nav>
  <?php endif; ?>
</header>
<main class="container">
