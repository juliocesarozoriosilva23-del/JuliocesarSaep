<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_login();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    if ($action === 'save') {
        $id = (int)(isset($_POST['id']) ? $_POST['id'] : 0);
        $cliente = (int)(isset($_POST['cliente_id']) ? $_POST['cliente_id'] : 0);
        $tipo = trim(isset($_POST['tipo']) ? $_POST['tipo'] : '');
        $marca = trim(isset($_POST['marca']) ? $_POST['marca'] : '');
        $modelo = trim(isset($_POST['modelo']) ? $_POST['modelo'] : '');
        $serie = trim(isset($_POST['numero_serie']) ? $_POST['numero_serie'] : '');
        $descricao = trim(isset($_POST['descricao']) ? $_POST['descricao'] : '');
        if (!$cliente || $tipo === '' || $marca === '' || $modelo === '') {
            $error = 'Selecione um cliente e preencha tipo, marca e modelo.';
        } else {
            if ($id) {
                db_exec('UPDATE equipamentos SET cliente_id=?,tipo=?,marca=?,modelo=?,numero_serie=?,descricao=? WHERE id=?',
                    'isssssi', array($cliente, $tipo, $marca, $modelo, $serie !== '' ? $serie : null, $descricao !== '' ? $descricao : null, $id));
            } else {
                db_exec('INSERT INTO equipamentos (cliente_id,tipo,marca,modelo,numero_serie,descricao) VALUES (?,?,?,?,?,?)',
                    'isssss', array($cliente, $tipo, $marca, $modelo, $serie !== '' ? $serie : null, $descricao !== '' ? $descricao : null));
            }
            redirect('equipamentos.php?salvo=1');
        }
    } elseif ($action === 'delete') {
        try {
            db_exec('DELETE FROM equipamentos WHERE id=?', 'i', array((int)$_POST['id']));
            redirect('equipamentos.php?excluido=1');
        } catch (Exception $e) {
            $error = 'Não é possível excluir: há ordens de serviço vinculadas.';
        }
    }
}
$clientes = db_all('SELECT id,nome FROM clientes ORDER BY nome');
$edit = null;
if (isset($_GET['editar'])) $edit = db_one('SELECT * FROM equipamentos WHERE id=?', 'i', array((int)$_GET['editar']));
$rows = db_all('SELECT e.*,c.nome AS cliente_nome FROM equipamentos e JOIN clientes c ON c.id=e.cliente_id ORDER BY e.id DESC');
$pageTitle = 'Equipamentos';
require __DIR__ . '/includes/header.php';
?>
<h1>Equipamentos</h1>
<?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<?php if (isset($_GET['salvo'])): ?><div class="alert success">Equipamento salvo com sucesso.</div><?php endif; ?>
<?php if (isset($_GET['excluido'])): ?><div class="alert success">Equipamento excluído.</div><?php endif; ?>
<section class="card"><h2><?= $edit ? 'Editar equipamento' : 'Cadastrar equipamento' ?></h2>
<?php if (!$clientes): ?><p>Cadastre um cliente antes de cadastrar equipamentos.</p><?php else: ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
<div class="form-grid">
<div><label>Cliente *</label><select name="cliente_id" required><option value="">Selecione...</option><?php foreach($clientes as $c): ?><option value="<?= (int)$c['id'] ?>" <?= (int)($edit['cliente_id'] ?? 0)===(int)$c['id']?'selected':'' ?>><?= e($c['nome']) ?></option><?php endforeach; ?></select></div>
<div><label>Tipo *</label><input name="tipo" required value="<?= e($edit['tipo'] ?? '') ?>" placeholder="Notebook, desktop, impressora..."></div>
<div><label>Marca *</label><input name="marca" required value="<?= e($edit['marca'] ?? '') ?>"></div>
<div><label>Modelo *</label><input name="modelo" required value="<?= e($edit['modelo'] ?? '') ?>"></div>
<div><label>Número de série</label><input name="numero_serie" value="<?= e($edit['numero_serie'] ?? '') ?>"></div>
<div><label>Descrição</label><input name="descricao" value="<?= e($edit['descricao'] ?? '') ?>"></div>
</div><p><button>Salvar equipamento</button> <?php if($edit): ?><a class="btn secondary" href="equipamentos.php">Cancelar</a><?php endif; ?></p></form><?php endif; ?>
</section>
<section class="card"><h2>Equipamentos cadastrados</h2><div class="table-wrap"><table><thead><tr><th>Cliente</th><th>Tipo</th><th>Marca / modelo</th><th>Série</th><th>Ações</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><?= e($r['cliente_nome']) ?></td><td><?= e($r['tipo']) ?></td><td><?= e($r['marca'].' '.$r['modelo']) ?></td><td><?= e($r['numero_serie']) ?></td><td><div class="actions"><a class="btn" href="?editar=<?= (int)$r['id'] ?>">Editar</a><form method="post" onsubmit="return confirm('Excluir equipamento?')"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="danger">Excluir</button></form></div></td></tr><?php endforeach; ?>
<?php if(!$rows): ?><tr><td colspan="5">Nenhum equipamento cadastrado.</td></tr><?php endif; ?></tbody></table></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
