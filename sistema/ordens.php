<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_login();
$error = '';
$statuses = array('Aberta','Em análise','Aguardando peça','Em reparo','Concluída','Cancelada');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    if ($action === 'save') {
        $id = (int)(isset($_POST['id']) ? $_POST['id'] : 0);
        $cliente = (int)(isset($_POST['cliente_id']) ? $_POST['cliente_id'] : 0);
        $equip = (int)(isset($_POST['equipamento_id']) ? $_POST['equipamento_id'] : 0);
        $problema = trim(isset($_POST['problema']) ? $_POST['problema'] : '');
        $diagnostico = trim(isset($_POST['diagnostico']) ? $_POST['diagnostico'] : '');
        $status = isset($_POST['status']) ? $_POST['status'] : 'Aberta';
        $valor = (float)(isset($_POST['valor']) ? $_POST['valor'] : 0);

        if (!$cliente || !$equip || $problema === '' || !in_array($status, $statuses, true) || $valor < 0) {
            $error = 'Preencha cliente, equipamento, problema, status e valor válido.';
        } else {
            $check = (int)db_value('SELECT COUNT(*) AS total FROM equipamentos WHERE id=? AND cliente_id=?', 'ii', array($equip, $cliente));
            if (!$check) {
                $error = 'O equipamento selecionado não pertence ao cliente escolhido.';
            } else {
                if ($id) {
                    db_exec('UPDATE ordens_servico SET cliente_id=?, equipamento_id=?, problema=?, diagnostico=?, status=?, valor=?, data_conclusao=IF(?="Concluída",COALESCE(data_conclusao,NOW()),NULL) WHERE id=?',
                        'iisssdsi', array($cliente, $equip, $problema, $diagnostico !== '' ? $diagnostico : null, $status, $valor, $status, $id));
                } else {
                    db_exec('INSERT INTO ordens_servico (cliente_id,equipamento_id,problema,diagnostico,status,valor) VALUES (?,?,?,?,?,?)',
                        'iisssd', array($cliente, $equip, $problema, $diagnostico !== '' ? $diagnostico : null, $status, $valor));
                }
                redirect('ordens.php?salvo=1');
            }
        }
    } elseif ($action === 'delete') {
        db_exec('DELETE FROM ordens_servico WHERE id=?', 'i', array((int)$_POST['id']));
        redirect('ordens.php?excluido=1');
    }
}
$clientes = db_all('SELECT id,nome FROM clientes ORDER BY nome');
$equipamentos = db_all('SELECT e.id,e.cliente_id,e.tipo,e.marca,e.modelo,c.nome AS cliente_nome FROM equipamentos e JOIN clientes c ON c.id=e.cliente_id ORDER BY c.nome,e.tipo');
$edit = null;
if (isset($_GET['editar'])) $edit = db_one('SELECT * FROM ordens_servico WHERE id=?', 'i', array((int)$_GET['editar']));
$rows = db_all('SELECT os.*,c.nome AS cliente_nome,e.tipo,e.marca,e.modelo FROM ordens_servico os JOIN clientes c ON c.id=os.cliente_id JOIN equipamentos e ON e.id=os.equipamento_id ORDER BY os.data_abertura DESC, os.id DESC');
$pageTitle = 'Ordens de serviço';
require __DIR__ . '/includes/header.php';
?>
<h1>Ordens de serviço</h1>
<?php if($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<?php if(isset($_GET['salvo'])): ?><div class="alert success">Ordem de serviço salva.</div><?php endif; ?>
<?php if(isset($_GET['excluido'])): ?><div class="alert success">Ordem de serviço excluída.</div><?php endif; ?>
<section class="card"><h2><?= $edit ? 'Editar ordem de serviço' : 'Abrir ordem de serviço' ?></h2>
<?php if(!$clientes || !$equipamentos): ?><p>Cadastre pelo menos um cliente e um equipamento antes de abrir uma ordem de serviço.</p><?php else: ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
<div class="form-grid">
<div><label>Cliente *</label><select name="cliente_id" required><option value="">Selecione...</option><?php foreach($clientes as $c): ?><option value="<?= (int)$c['id'] ?>" <?= (int)($edit['cliente_id'] ?? 0)===(int)$c['id']?'selected':'' ?>><?= e($c['nome']) ?></option><?php endforeach; ?></select></div>
<div><label>Equipamento *</label><select name="equipamento_id" required><option value="">Selecione...</option><?php foreach($equipamentos as $eq): ?><option value="<?= (int)$eq['id'] ?>" <?= (int)($edit['equipamento_id'] ?? 0)===(int)$eq['id']?'selected':'' ?>><?= e($eq['cliente_nome'].' — '.$eq['tipo'].' '.$eq['marca'].' '.$eq['modelo']) ?></option><?php endforeach; ?></select></div>
<div><label>Status</label><select name="status"><?php foreach($statuses as $s): ?><option <?= ($edit['status'] ?? 'Aberta')===$s?'selected':'' ?>><?= e($s) ?></option><?php endforeach; ?></select></div>
<div><label>Valor estimado (R$)</label><input type="number" min="0" step="0.01" name="valor" value="<?= e((string)($edit['valor'] ?? '0.00')) ?>"></div>
<div><label>Problema relatado *</label><textarea name="problema" required><?= e($edit['problema'] ?? '') ?></textarea></div>
<div><label>Diagnóstico</label><textarea name="diagnostico"><?= e($edit['diagnostico'] ?? '') ?></textarea></div>
</div><p><button>Salvar ordem</button> <?php if($edit): ?><a class="btn secondary" href="ordens.php">Cancelar</a><?php endif; ?></p></form><?php endif; ?>
</section>
<section class="card"><h2>Ordens de serviço</h2><p class="muted">Ordenadas pela data de abertura, da mais recente para a mais antiga.</p><div class="table-wrap"><table><thead><tr><th>Nº / abertura</th><th>Cliente</th><th>Equipamento</th><th>Problema / diagnóstico</th><th>Status</th><th>Valor</th><th>Ações</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td>#<?= (int)$r['id'] ?><br><span class="small"><?= e(date('d/m/Y H:i',strtotime($r['data_abertura']))) ?></span></td><td><?= e($r['cliente_nome']) ?></td><td><?= e($r['tipo'].' '.$r['marca'].' '.$r['modelo']) ?></td><td><?= e($r['problema']) ?><?php if($r['diagnostico']): ?><br><span class="muted small">Diagnóstico: <?= e($r['diagnostico']) ?></span><?php endif; ?></td><td><span class="pill"><?= e($r['status']) ?></span></td><td>R$ <?= number_format((float)$r['valor'],2,',','.') ?></td><td><div class="actions"><a class="btn" href="?editar=<?= (int)$r['id'] ?>">Editar</a><form method="post" onsubmit="return confirm('Excluir esta ordem?')"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="danger">Excluir</button></form></div></td></tr><?php endforeach; ?>
<?php if(!$rows): ?><tr><td colspan="7">Nenhuma ordem cadastrada.</td></tr><?php endif; ?></tbody></table></div></section>
<?php require __DIR__.'/includes/footer.php'; ?>
