<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_login();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    if ($action === 'save') {
        $id = (int)(isset($_POST['id']) ? $_POST['id'] : 0);
        $nome = trim(isset($_POST['nome']) ? $_POST['nome'] : '');
        $cpf = preg_replace('/\D+/', '', isset($_POST['cpf']) ? $_POST['cpf'] : '');
        $telefone = trim(isset($_POST['telefone']) ? $_POST['telefone'] : '');
        $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
        $endereco = trim(isset($_POST['endereco']) ? $_POST['endereco'] : '');

        if ($nome === '' || $telefone === '' || ($cpf !== '' && strlen($cpf) !== 11)) {
            $error = 'Informe nome e telefone. Se preencher CPF, use 11 dígitos.';
        } else {
            $cpfEnc = cpf_encrypt($cpf);
            if ($id > 0) {
                db_exec('UPDATE clientes SET nome=?, cpf_criptografado=?, telefone=?, email=?, endereco=? WHERE id=?',
                    'sssssi', array($nome, $cpfEnc, $telefone, $email !== '' ? $email : null, $endereco !== '' ? $endereco : null, $id));
            } else {
                db_exec('INSERT INTO clientes (nome,cpf_criptografado,telefone,email,endereco) VALUES (?,?,?,?,?)',
                    'sssss', array($nome, $cpfEnc, $telefone, $email !== '' ? $email : null, $endereco !== '' ? $endereco : null));
            }
            redirect('clientes.php?salvo=1');
        }
    } elseif ($action === 'delete') {
        $id = (int)(isset($_POST['id']) ? $_POST['id'] : 0);
        try {
            db_exec('DELETE FROM clientes WHERE id=?', 'i', array($id));
            redirect('clientes.php?excluido=1');
        } catch (Exception $e) {
            $error = 'Não é possível excluir: há equipamentos ou ordens vinculados a este cliente.';
        }
    }
}

$edit = null;
if (isset($_GET['editar'])) $edit = db_one('SELECT * FROM clientes WHERE id=?', 'i', array((int)$_GET['editar']));
$busca = trim(isset($_GET['q']) ? $_GET['q'] : '');
if ($busca !== '') {
    $like = '%' . $busca . '%';
    $rows = db_all('SELECT * FROM clientes WHERE nome LIKE ? OR telefone LIKE ? ORDER BY nome', 'ss', array($like, $like));
} else {
    $rows = db_all('SELECT * FROM clientes ORDER BY nome');
}
$pageTitle = 'Clientes';
require __DIR__ . '/includes/header.php';
?>
<h1>Clientes</h1>
<?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<?php if (isset($_GET['salvo'])): ?><div class="alert success">Cliente salvo com sucesso.</div><?php endif; ?>
<?php if (isset($_GET['excluido'])): ?><div class="alert success">Cliente excluído.</div><?php endif; ?>
<section class="card">
<h2><?= $edit ? 'Editar cliente' : 'Cadastrar cliente' ?></h2>
<form method="post">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="save">
<input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
<div class="form-grid">
<div><label>Nome *</label><input name="nome" required value="<?= e($edit['nome'] ?? '') ?>"></div>
<div><label>CPF (opcional)</label><input name="cpf" inputmode="numeric" maxlength="14" placeholder="Somente para cadastro/atualização" value="<?= $edit ? e(cpf_decrypt($edit['cpf_criptografado'])) : '' ?>"></div>
<div><label>Telefone *</label><input name="telefone" required value="<?= e($edit['telefone'] ?? '') ?>"></div>
<div><label>E-mail</label><input name="email" type="email" value="<?= e($edit['email'] ?? '') ?>"></div>
<div><label>Endereço</label><input name="endereco" value="<?= e($edit['endereco'] ?? '') ?>"></div>
</div><p><button type="submit">Salvar cliente</button> <?php if ($edit): ?><a class="btn secondary" href="clientes.php">Cancelar edição</a><?php endif; ?></p>
</form>
<p class="small muted">O CPF é armazenado criptografado. Os registros de demonstração do SQL não contêm CPF real.</p>
</section>
<section class="card">
<h2>Lista de clientes</h2>
<form class="search" method="get"><input name="q" placeholder="Buscar por nome ou telefone" value="<?= e($busca) ?>"><button>Buscar</button><a class="btn secondary" href="clientes.php">Limpar</a></form>
<div class="table-wrap"><table><thead><tr><th>Nome</th><th>CPF</th><th>Telefone</th><th>E-mail</th><th>Ações</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td><?= e($r['nome']) ?></td><td><?= e(cpf_decrypt($r['cpf_criptografado'])) ?></td><td><?= e($r['telefone']) ?></td><td><?= e($r['email']) ?></td><td><div class="actions"><a class="btn" href="?editar=<?= (int)$r['id'] ?>">Editar</a><form method="post" onsubmit="return confirm('Excluir este cliente?')"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="danger">Excluir</button></form></div></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="5">Nenhum cliente encontrado.</td></tr><?php endif; ?>
</tbody></table></div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
