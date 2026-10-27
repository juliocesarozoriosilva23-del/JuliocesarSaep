<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if (!empty($_SESSION['usuario_id'])) redirect('index.php');
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim(isset($_POST['usuario']) ? $_POST['usuario'] : '');
    $senha = isset($_POST['senha']) ? $_POST['senha'] : '';
    $user = db_one('SELECT id, nome, usuario, senha_hash FROM usuarios WHERE usuario = ? LIMIT 1', 's', array($usuario));

    if ($user && password_verify($senha, $user['senha_hash'])) {
        session_regenerate_id(true);
        $_SESSION['usuario_id'] = (int)$user['id'];
        $_SESSION['usuario_nome'] = $user['nome'];
        $_SESSION['ultima_atividade'] = time();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        redirect('index.php');
    }
    $error = 'Usuário ou senha inválidos. Confira os dados e tente novamente.';
}
$pageTitle = 'Entrar';
require __DIR__ . '/includes/header.php';
?>
<div class="card login">
<h1>Entrar no sistema</h1>
<p class="muted">Informe suas credenciais para acessar os cadastros.</p>
<?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<?php if (isset($_GET['expirada'])): ?><div class="alert">Sua sessão expirou por inatividade. Entre novamente.</div><?php endif; ?>
<form method="post">
<div style="margin-bottom:14px"><label for="usuario">Usuário</label><input id="usuario" name="usuario" required autocomplete="username"></div>
<div style="margin-bottom:18px"><label for="senha">Senha</label><input id="senha" name="senha" type="password" required autocomplete="current-password"></div>
<button type="submit">Entrar</button>
</form>
<p class="small muted">Acesso de demonstração: admin / Admin@123</p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
