<?php
date_default_timezone_set('America/Lima');
require_once __DIR__ . '/../admin/classes/GuiaRemision.php';
require_once __DIR__ . '/../admin/classes/Database.php';

$id = (int)($_GET['id'] ?? 0);
$g = (new GuiaRemision())->getById($id);
if (!$g) die('Guía no encontrada');

$db = new Database();
$con = $db->connect();
$r = $con->query("SELECT * FROM emisor WHERE id = 1 LIMIT 1");
$em = $r ? $r->fetch_assoc() : [];

$motivos = GuiaRemision::motivosTraslado();
$modalidades = GuiaRemision::modalidades();
$num = $g['serie'] . '-' . str_pad($g['correlativo'], 6, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Guía <?= $num ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; color: #1f2937; background: #f3f4f6; font-size: 13px; }
        .sheet { width: 210mm; min-height: 280mm; margin: 12px auto; background: #fff; padding: 26px 30px; box-shadow: 0 8px 30px rgba(0,0,0,.12); }
        .top { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #0d9488; padding-bottom: 14px; }
        .top h1 { font-size: 18px; color: #0f172a; }
        .top .sub { color: #6b7280; font-size: 12px; margin-top: 2px; }
        .box { border: 1.5px solid #0d9488; border-radius: 8px; padding: 10px 14px; text-align: center; min-width: 230px; }
        .box .t { font-size: 12px; color: #0d9488; font-weight: bold; text-transform: uppercase; letter-spacing: .04em; }
        .box .n { font-size: 18px; font-weight: bold; margin-top: 2px; }
        .box .r { font-size: 12px; color: #6b7280; }
        h2 { font-size: 12px; text-transform: uppercase; letter-spacing: .05em; color: #0d9488; margin: 18px 0 6px; border-bottom: 1px solid #e5e7eb; padding-bottom: 3px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px 24px; }
        .f { display: flex; gap: 6px; padding: 2px 0; }
        .f b { color: #374151; min-width: 130px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th { background: #f0fdfa; color: #0f766e; text-align: left; padding: 7px 8px; font-size: 11px; text-transform: uppercase; border-bottom: 1px solid #ccebe7; }
        td { padding: 7px 8px; border-bottom: 1px solid #f0f0f0; }
        .foot { margin-top: 24px; text-align: center; color: #9ca3af; font-size: 11px; }
        .bar { text-align: center; padding: 14px; }
        .bar button { background: #0d9488; color: #fff; border: 0; padding: 9px 22px; border-radius: 8px; font-size: 14px; cursor: pointer; margin: 0 4px; }
        .bar .g { background: #e5e7eb; color: #374151; }
        @media print { body { background: #fff; } .sheet { box-shadow: none; margin: 0; } .no-print { display: none; } @page { margin: 8mm; } }
    </style>
</head>
<body>
<div class="bar no-print">
    <button onclick="window.print()">🖨 Imprimir</button>
    <button class="g" onclick="window.close(); setTimeout(function(){ window.location.href='guias.php'; }, 150);">Cerrar</button>
</div>

<div class="sheet">
    <div class="top">
        <div>
            <h1><?= htmlspecialchars($em['nom_comercial'] ?? $em['razon_social'] ?? 'EMPRESA') ?></h1>
            <div class="sub"><?= htmlspecialchars($em['razon_social'] ?? '') ?></div>
            <div class="sub">RUC: <?= htmlspecialchars($em['ruc'] ?? '') ?></div>
            <div class="sub"><?= htmlspecialchars($em['direccion'] ?? '') ?></div>
        </div>
        <div class="box">
            <div class="t">Guía de Remisión Electrónica</div>
            <div class="n"><?= $num ?></div>
            <div class="r">Remitente · Tipo 09</div>
        </div>
    </div>

    <h2>Datos del traslado</h2>
    <div class="grid">
        <div class="f"><b>Fecha de emisión:</b> <?= date('d/m/Y H:i', strtotime($g['fecha_emision'])) ?></div>
        <div class="f"><b>Fecha de traslado:</b> <?= date('d/m/Y', strtotime($g['fecha_traslado'])) ?></div>
        <div class="f"><b>Motivo:</b> <?= htmlspecialchars($motivos[$g['motivo_traslado']] ?? $g['motivo_traslado']) ?></div>
        <div class="f"><b>Modalidad:</b> <?= htmlspecialchars($modalidades[$g['modalidad_transporte']] ?? $g['modalidad_transporte']) ?></div>
        <div class="f"><b>Peso total:</b> <?= number_format($g['peso_total'], 2) ?> <?= htmlspecialchars($g['unidad_peso']) ?></div>
        <div class="f"><b>Estado SUNAT:</b> <?= strtoupper($g['sunat_estado'] ?? 'pendiente') ?></div>
    </div>

    <h2>Destinatario</h2>
    <div class="grid">
        <div class="f"><b>Nombre / Razón social:</b> <?= htmlspecialchars($g['cliente_nombre'] ?? '-') ?></div>
        <div class="f"><b>Documento:</b> <?= htmlspecialchars(($g['cliente_tipo'] ?? '') . ' ' . ($g['cliente_doc'] ?? '-')) ?></div>
    </div>

    <h2>Punto de partida y llegada</h2>
    <div class="grid">
        <div class="f"><b>Partida (ubigeo):</b> <?= htmlspecialchars($g['ubigeo_partida']) ?></div>
        <div class="f"><b>Llegada (ubigeo):</b> <?= htmlspecialchars($g['ubigeo_llegada']) ?></div>
        <div class="f"><b>Dirección partida:</b> <?= htmlspecialchars($g['dir_partida'] ?? '-') ?></div>
        <div class="f"><b>Dirección llegada:</b> <?= htmlspecialchars($g['dir_llegada'] ?? '-') ?></div>
    </div>

    <h2>Transporte</h2>
    <div class="grid">
        <?php if ($g['modalidad_transporte'] === '01'): ?>
            <div class="f"><b>Transportista (RUC):</b> <?= htmlspecialchars($g['transportista_ruc'] ?: '-') ?></div>
            <div class="f"><b>Razón social:</b> <?= htmlspecialchars($g['transportista_razon'] ?: '-') ?></div>
            <div class="f"><b>Registro MTC:</b> <?= htmlspecialchars($g['transportista_mtc'] ?: '-') ?></div>
        <?php else: ?>
            <div class="f"><b>Placa:</b> <?= htmlspecialchars($g['vehiculo_placa'] ?: '-') ?></div>
            <div class="f"><b>Conductor:</b> <?= htmlspecialchars(trim(($g['conductor_nombres'] ?? '') . ' ' . ($g['conductor_apellidos'] ?? '')) ?: '-') ?></div>
            <div class="f"><b>DNI conductor:</b> <?= htmlspecialchars($g['conductor_doc'] ?: '-') ?></div>
            <div class="f"><b>Licencia:</b> <?= htmlspecialchars($g['conductor_licencia'] ?: '-') ?></div>
        <?php endif; ?>
    </div>

    <h2>Bienes trasladados</h2>
    <table>
        <thead><tr><th>Código</th><th>Descripción</th><th>Unidad</th><th style="text-align:right">Cantidad</th></tr></thead>
        <tbody>
            <?php foreach ($g['detalle'] as $d): ?>
                <tr>
                    <td><?= htmlspecialchars($d['codigo']) ?></td>
                    <td><?= htmlspecialchars($d['descripcion']) ?></td>
                    <td><?= htmlspecialchars($d['unidad']) ?></td>
                    <td style="text-align:right"><?= (int)$d['cantidad'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="foot">
        Representación impresa de la Guía de Remisión Electrónica · Consulte en www.sunat.gob.pe
    </div>
</div>
</body>
</html>
