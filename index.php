<?php
require __DIR__ . '/config.php';

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$mensaje = '';
$error = '';
$editar = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $destinatario = trim($_POST['destinatario'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    try {
        if ($accion === 'crear' || $accion === 'actualizar') {
            if ($destinatario === '' || $direccion === '' || $descripcion === '') {
                throw new RuntimeException('Completa todos los campos.');
            }
            if (mb_strlen($destinatario) > 150 || mb_strlen($direccion) > 255) {
                throw new RuntimeException('El destinatario o la dirección superan la longitud permitida.');
            }

            if ($accion === 'crear') {
                $stmt = $pdo->prepare('INSERT INTO envios (destinatario, direccion, descripcion) VALUES (?, ?, ?)');
                $stmt->execute([$destinatario, $direccion, $descripcion]);
                header('Location: index.php?estado=creado');
                exit;
            } else {
                if (!$id) throw new RuntimeException('Identificador no válido.');
                $stmt = $pdo->prepare('UPDATE envios SET destinatario = ?, direccion = ?, descripcion = ? WHERE id = ?');
                $stmt->execute([$destinatario, $direccion, $descripcion, $id]);
                header('Location: index.php?estado=actualizado');
                exit;
            }
        } elseif ($accion === 'eliminar') {
            if (!$id) throw new RuntimeException('Identificador no válido.');
            $stmt = $pdo->prepare('DELETE FROM envios WHERE id = ?');
            $stmt->execute([$id]);
            header('Location: index.php?estado=eliminado');
            exit;
        }
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    } catch (PDOException $e) {
        $error = 'No se pudo completar la operación. Inténtalo de nuevo.';
    }
}

if (isset($_GET['editar'])) {
    $idEditar = filter_input(INPUT_GET, 'editar', FILTER_VALIDATE_INT);
    if ($idEditar) {
        $stmt = $pdo->prepare('SELECT * FROM envios WHERE id = ?');
        $stmt->execute([$idEditar]);
        $editar = $stmt->fetch() ?: null;
    }
}

$envios = $pdo->query('SELECT * FROM envios ORDER BY creado_en DESC, id DESC')->fetchAll();
$estados = [
    'creado' => 'Envío registrado correctamente.',
    'actualizado' => 'Envío actualizado correctamente.',
    'eliminado' => 'Envío eliminado correctamente.'
];
if (isset($_GET['estado'], $estados[$_GET['estado']])) $mensaje = $estados[$_GET['estado']];
$valores = $editar ?: ['id' => '', 'destinatario' => '', 'direccion' => '', 'descripcion' => ''];
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Gestión de envíos</title>
<style>
:root{font-family:Arial,sans-serif;color:#172033;background:#f3f6fb}
*{box-sizing:border-box}body{margin:0}header{background:#183b67;color:#fff;padding:24px max(20px,calc((100% - 1100px)/2))}
header h1{margin:0 0 6px;font-size:27px}header p{margin:0;color:#dce8f8}
main{max-width:1100px;margin:28px auto;padding:0 18px;display:grid;gap:22px;grid-template-columns: minmax(280px, .8fr) minmax(0, 1.6fr)}
.card{background:white;border-radius:14px;padding:22px;box-shadow:0 5px 22px #17365d12}
h2{font-size:20px;margin:0 0 18px}label{display:block;font-weight:700;font-size:14px;margin:14px 0 7px}
input,textarea{width:100%;padding:11px 12px;border:1px solid #cbd5e1;border-radius:8px;font:inherit}textarea{min-height:105px;resize:vertical}
button,.btn{display:inline-block;border:0;border-radius:8px;padding:10px 13px;font-weight:700;cursor:pointer;text-decoration:none;font-size:14px}
.primary{background:#2367c9;color:white}.muted{background:#e7edf5;color:#23344d}.danger{background:#fee2e2;color:#991b1b}.actions{display:flex;gap:7px;flex-wrap:wrap}
.notice{padding:12px 14px;border-radius:8px;margin-bottom:14px;background:#dcfce7;color:#166534}.error{background:#fee2e2;color:#991b1b}
.table-wrap{overflow-x:auto}table{width:100%;border-collapse:collapse;font-size:14px}th,td{text-align:left;padding:12px 10px;border-bottom:1px solid #e7edf4;vertical-align:top}th{background:#f8fafc;color:#475569}td.description{min-width:160px;white-space:pre-wrap}.empty{color:#64748b;padding:18px 0}
@media(max-width:800px){main{grid-template-columns:1fr}header h1{font-size:23px}}
</style>
</head>
<body>
<header><h1>Gestión de envíos</h1><p>Registra, consulta, modifica y elimina envíos.</p></header>
<main>
<section class="card">
<h2><?= $editar ? 'Editar envío' : 'Registrar envío' ?></h2>
<?php if ($mensaje): ?><div class="notice"><?= h($mensaje) ?></div><?php endif; ?>
<?php if ($error): ?><div class="notice error"><?= h($error) ?></div><?php endif; ?>
<form method="post" action="index.php">
<input type="hidden" name="accion" value="<?= $editar ? 'actualizar' : 'crear' ?>">
<?php if ($editar): ?><input type="hidden" name="id" value="<?= h($valores['id']) ?>"><?php endif; ?>
<label for="destinatario">Destinatario</label>
<input id="destinatario" name="destinatario" maxlength="150" required value="<?= h($valores['destinatario']) ?>" placeholder="Nombre del destinatario">
<label for="direccion">Dirección</label>
<input id="direccion" name="direccion" maxlength="255" required value="<?= h($valores['direccion']) ?>" placeholder="Dirección de entrega">
<label for="descripcion">Descripción</label>
<textarea id="descripcion" name="descripcion" required placeholder="Descripción del paquete o envío"><?= h($valores['descripcion']) ?></textarea>
<div class="actions" style="margin-top:18px">
<button class="primary" type="submit"><?= $editar ? 'Guardar cambios' : 'Crear envío' ?></button>
<?php if ($editar): ?><a class="btn muted" href="index.php">Cancelar</a><?php endif; ?>
</div>
</form>
</section>
<section class="card">
<h2>Envíos registrados (<?= count($envios) ?>)</h2>
<?php if (!$envios): ?>
<p class="empty">Todavía no hay envíos. Usa el formulario para registrar el primero.</p>
<?php else: ?>
<div class="table-wrap"><table>
<thead><tr><th>ID</th><th>Destinatario / dirección</th><th>Descripción</th><th>Acciones</th></tr></thead>
<tbody>
<?php foreach ($envios as $envio): ?>
<tr>
<td><?= h($envio['id']) ?></td>
<td><strong><?= h($envio['destinatario']) ?></strong><br><?= h($envio['direccion']) ?></td>
<td class="description"><?= h($envio['descripcion']) ?></td>
<td><div class="actions">
<a class="btn muted" href="index.php?editar=<?= h($envio['id']) ?>">Editar</a>
<form method="post" action="index.php" onsubmit="return confirm('¿Seguro que deseas eliminar este envío?');">
<input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?= h($envio['id']) ?>">
<button class="danger" type="submit">Eliminar</button>
</form>
</div></td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
<?php endif; ?>
</section>
</main>
</body>
</html>
