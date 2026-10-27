<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_login();
$pageTitle = 'Painel principal';
$clientes = (int)db_value('SELECT COUNT(*) AS total FROM clientes');
$equipamentos = (int)db_value('SELECT COUNT(*) AS total FROM equipamentos');
$ordens = (int)db_value('SELECT COUNT(*) AS total FROM ordens_servico');
$abertas = (int)db_value("SELECT COUNT(*) AS total FROM ordens_servico WHERE status NOT IN ('Concluída','Cancelada')");
require __DIR__ . '/includes/header.php';
?>
<section class="card">
<h1>Olá, <?= e(current_user()) ?>!</h1>
<p>Bem-vindo ao sistema de controle de atendimentos da assistência técnica.</p>
<div class="actions"><a class="btn" href="clientes.php">Gerenciar clientes</a><a class="btn" href="equipamentos.php">Gerenciar equipamentos</a><a class="btn" href="ordens.php">Gerenciar ordens de serviço</a><a class="btn secondary" href="logout.php">Sair do sistema</a></div>
</section>
<div class="grid">
 <section class="card stat"><h3>Clientes</h3><p><?= $clientes ?></p></section>
 <section class="card stat"><h3>Equipamentos</h3><p><?= $equipamentos ?></p></section>
 <section class="card stat"><h3>Total de OS</h3><p><?= $ordens ?></p></section>
 <section class="card stat"><h3>OS em andamento</h3><p><?= $abertas ?></p></section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
