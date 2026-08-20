<?php
// =============================================
// DASHBOARD DE RESULTADOS
// SISTEMA DE EVALUACIÓN DE CUMPLIMIENTO LOPDP
// PROYECTO DE TITULACIÓN - ISMAC
// =============================================
require_once 'config/conexion.php';

$evaluacion_id = $_GET['id'] ?? 0;
if (!$evaluacion_id) {
    header("Location: index.php");
    exit;
}

$evaluacion = obtenerEvaluacion($evaluacion_id);
$respuestas = obtenerRespuestas($evaluacion_id);
$hallazgos = obtenerHallazgos($evaluacion_id);
$conclusiones = obtenerConclusiones($evaluacion_id);
$porcentajes = calcularPorcentajes($evaluacion_id);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Evaluación LOPDP</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
<div class="container">
    <div class="header">
        <h1>📊 Dashboard de Resultados</h1>
        <p>Evaluación del sistema biométrico - Carrera de Desarrollo de Software</p>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div style="background:#e8f5e9;padding:15px;border-radius:8px;margin-bottom:20px;border-left:4px solid #2e7d32;">
            ✅ Evaluación guardada exitosamente. ID: #<?php echo $evaluacion_id; ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2>📋 Información de la Evaluación</h2>
        <table class="tabla-resultados">
            <tr><th>Institución</th><td><?php echo htmlspecialchars($evaluacion['nombre_institucion']); ?></td></tr>
            <tr><th>RUC</th><td><?php echo htmlspecialchars($evaluacion['ruc'] ?: 'No registrado'); ?></td></tr>
            <tr><th>Sistema Evaluado</th><td><?php echo htmlspecialchars($evaluacion['nombre_sistema']); ?></td></tr>
            <tr><th>Fecha</th><td><?php echo date('d/m/Y', strtotime($evaluacion['fecha_evaluacion'])); ?></td></tr>
            <tr><th>Evaluador</th><td><?php echo htmlspecialchars($evaluacion['evaluador']); ?></td></tr>
        </table>
    </div>

    <div class="dashboard-grid">
        <div class="dashboard-card">
            <h3>Categoría 1</h3>
            <p style="color:#666;font-size:0.9rem;">Políticas Institucionales</p>
            <div class="numero <?php echo $porcentajes[1] >= 80 ? 'color-verde' : ($porcentajes[1] >= 50 ? 'color-amarillo' : 'color-rojo'); ?>">
                <?php echo $porcentajes[1]; ?>%
            </div>
            <div class="barra-progreso">
                <div class="barra" style="width:<?php echo $porcentajes[1]; ?>%;"></div>
            </div>
        </div>
        <div class="dashboard-card">
            <h3>Categoría 2</h3>
            <p style="color:#666;font-size:0.9rem;">Sistema Biométrico</p>
            <div class="numero <?php echo $porcentajes[2] >= 80 ? 'color-verde' : ($porcentajes[2] >= 50 ? 'color-amarillo' : 'color-rojo'); ?>">
                <?php echo $porcentajes[2]; ?>%
            </div>
            <div class="barra-progreso">
                <div class="barra" style="width:<?php echo $porcentajes[2]; ?>%;"></div>
            </div>
        </div>
        <div class="dashboard-card">
            <h3>Categoría 3</h3>
            <p style="color:#666;font-size:0.9rem;">Actores del Sistema</p>
            <div class="numero <?php echo $porcentajes[3] >= 80 ? 'color-verde' : ($porcentajes[3] >= 50 ? 'color-amarillo' : 'color-rojo'); ?>">
                <?php echo $porcentajes[3]; ?>%
            </div>
            <div class="barra-progreso">
                <div class="barra" style="width:<?php echo $porcentajes[3]; ?>%;"></div>
            </div>
        </div>
    </div>

    <?php if (!empty($hallazgos)): ?>
    <div class="card">
        <h2>🔍 Hallazgos Identificados</h2>
        <?php foreach ($hallazgos as $h): ?>
            <div class="hallazgo-item">
                <div class="hallazgo-categoria">Categoría <?php echo $h['categoria']; ?> - Pregunta <?php echo $h['pregunta_id']; ?></div>
                <p><?php echo htmlspecialchars($h['descripcion']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ($conclusiones): ?>
    <div class="card">
        <h2>📄 Conclusiones</h2>
        <p><?php echo nl2br(htmlspecialchars($conclusiones['conclusiones'])); ?></p>
    </div>
    <?php endif; ?>

    <?php if ($conclusiones && $conclusiones['recomendaciones']): ?>
    <div class="card">
        <h2>💡 Recomendaciones</h2>
        <p><?php echo nl2br(htmlspecialchars($conclusiones['recomendaciones'])); ?></p>
    </div>
    <?php endif; ?>

    <div class="btn-grupo" style="margin-top:20px;">
        <a href="index.php" class="btn btn-primario">⬅ Nueva Evaluación</a>
        <button onclick="window.print()" class="btn btn-exito">🖨️ Imprimir / PDF</button>
    </div>
</div>
</body>
</html>