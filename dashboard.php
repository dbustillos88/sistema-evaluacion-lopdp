<?php
require_once 'config/conexion.php';

$evaluacionId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$evaluacionId || $evaluacionId < 1) {
    header('Location: index.php');
    exit;
}

$evaluacion = obtenerEvaluacion($evaluacionId);
if (!$evaluacion) {
    http_response_code(404);
    exit('Simulación no encontrada.');
}

$respuestas = obtenerRespuestas($evaluacionId);
$hallazgos = obtenerHallazgos($evaluacionId);
$conclusiones = obtenerConclusiones($evaluacionId) ?? ['conclusiones' => '', 'recomendaciones' => ''];
$metricas = calcularMetricasDesdeRespuestas($respuestas);

function e(?string $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function porcentaje(float $valor): string
{
    return number_format($valor, 2, ',', '.') . '%';
}

function claseNivel(string $nivel): string
{
    if ($nivel === 'Alto') return 'estado-alto';
    if ($nivel === 'Medio') return 'estado-medio';
    return 'estado-bajo';
}

$estados = $metricas['estados'];
$totalEstados = array_sum($estados);
$porcCumple = $totalEstados > 0 ? ($estados['Cumple totalmente'] / $totalEstados) * 100 : 0;
$porcParcial = $totalEstados > 0 ? ($estados['Cumple parcialmente'] / $totalEstados) * 100 : 0;
$porcNoCumple = $totalEstados > 0 ? ($estados['No cumple'] / $totalEstados) * 100 : 0;
$p1 = $porcCumple;
$p2 = $p1 + $porcParcial;
$p3 = $p2 + $porcNoCumple;
$donutBackground = $totalEstados > 0
    ? sprintf(
        'conic-gradient(#16a34a 0 %.4f%%,#d97706 %.4f%% %.4f%%,#dc2626 %.4f%% %.4f%%,#94a3b8 %.4f%% 100%%)',
        $p1, $p1, $p2, $p2, $p3, $p3
    )
    : '#e2e8f0';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard #<?php echo $evaluacionId; ?> - Simulador LOPDP</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
<div class="container">
    <div class="header">
        <div class="header-content">
            <div>
                <h1>Dashboard de resultados LOPDP</h1>
                <p class="subtitle">Simulación #<?php echo $evaluacionId; ?> · <?php echo e($evaluacion['nombre_sistema']); ?></p>
            </div>
            <span class="estado-badge <?php echo claseNivel($metricas['nivel']); ?>">
                Nivel de cumplimiento: <?php echo e($metricas['nivel']); ?>
            </span>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="mensaje-exito">La simulación se guardó correctamente. Los indicadores fueron calculados nuevamente desde la base de datos.</div>
    <?php endif; ?>

    <div class="card">
        <h2>Información de la simulación</h2>
        <table class="tabla-resultados">
            <tr><th>Institución</th><td><?php echo e($evaluacion['nombre_institucion']); ?></td></tr>
            <tr><th>RUC / Identificación</th><td><?php echo e($evaluacion['ruc'] ?: 'No registrado'); ?></td></tr>
            <tr><th>Sistema analizado</th><td><?php echo e($evaluacion['nombre_sistema']); ?></td></tr>
            <tr><th>Fecha de simulación</th><td><?php echo date('d/m/Y', strtotime($evaluacion['fecha_evaluacion'])); ?></td></tr>
            <tr><th>Responsable</th><td><?php echo e($evaluacion['evaluador']); ?></td></tr>
        </table>
    </div>

    <div class="dashboard-grid">
        <?php foreach ($metricas['categorias'] as $cat): ?>
            <?php
                $clase = $cat['porcentaje'] >= 80 ? 'color-verde' : ($cat['porcentaje'] >= 50 ? 'color-amarillo' : 'color-rojo');
                $barraClase = $cat['porcentaje'] >= 80 ? 'barra-verde' : ($cat['porcentaje'] >= 50 ? 'barra-amarillo' : 'barra-rojo');
            ?>
            <div class="dashboard-card">
                <div class="label"><?php echo e($cat['nombre']); ?></div>
                <div class="value <?php echo $clase; ?>"><?php echo porcentaje($cat['porcentaje']); ?></div>
                <div class="sub"><?php echo (int) $cat['total']; ?> preguntas · peso aplicable <?php echo number_format($cat['peso_aplicable'], 2, ',', '.'); ?>%</div>
                <div class="barra-progreso <?php echo $barraClase; ?>">
                    <div class="barra" style="width:<?php echo max(0, min(100, $cat['porcentaje'])); ?>%"></div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="dashboard-card dashboard-card-destacado">
            <div class="label">Promedio general</div>
            <div class="value"><?php echo porcentaje($metricas['promedio_general']); ?></div>
            <div class="sub"><?php echo (int) $metricas['total_preguntas']; ?> preguntas registradas</div>
        </div>
    </div>

    <div class="chart-container">
        <div class="chart-box">
            <h3>Nivel de cumplimiento por categoría</h3>
            <div class="bar-chart">
                <?php
                $barClasses = [1 => 'bar-primary', 2 => 'bar-success', 3 => 'bar-warning'];
                foreach ($metricas['categorias'] as $id => $cat):
                    $valor = max(0, min(100, (float) $cat['porcentaje']));
                ?>
                    <div class="bar-item <?php echo $barClasses[$id] ?? 'bar-primary'; ?>">
                        <span class="bar-label"><?php echo e($cat['nombre']); ?></span>
                        <div class="bar-track">
                            <div class="bar-fill" style="width:<?php echo $valor; ?>%;"><?php echo porcentaje($valor); ?></div>
                        </div>
                        <span class="bar-percent"><?php echo porcentaje($valor); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="chart-box">
            <h3>Distribución de estados</h3>
            <div class="donut-wrap">
                <div class="donut-chart" style="background:<?php echo e($donutBackground); ?>;">
                    <div class="donut-center"><strong><?php echo $totalEstados; ?></strong><span>respuestas</span></div>
                </div>
            </div>
            <div class="metrics-grid">
                <div class="metric-item"><span class="metric-dot" style="background:#16a34a"></span><span class="metric-text">Cumple totalmente</span><span class="metric-count"><?php echo $estados['Cumple totalmente']; ?></span></div>
                <div class="metric-item"><span class="metric-dot" style="background:#d97706"></span><span class="metric-text">Cumple parcialmente</span><span class="metric-count"><?php echo $estados['Cumple parcialmente']; ?></span></div>
                <div class="metric-item"><span class="metric-dot" style="background:#dc2626"></span><span class="metric-text">No cumple</span><span class="metric-count"><?php echo $estados['No cumple']; ?></span></div>
                <div class="metric-item"><span class="metric-dot" style="background:#94a3b8"></span><span class="metric-text">No aplica</span><span class="metric-count"><?php echo $estados['No aplica']; ?></span></div>
            </div>
        </div>
    </div>

    <div class="metodologia-nota">
        <strong>Metodología de cálculo:</strong> “Cumple totalmente” aporta el 100% del peso, “Cumple parcialmente” el 50%, “No cumple” el 0% y “No aplica” se excluye del denominador. El promedio general corresponde al promedio de las categorías con peso aplicable.
    </div>

    <?php if ($hallazgos): ?>
        <div class="card" style="margin-top:20px">
            <h2>Hallazgos identificados</h2>
            <?php foreach ($hallazgos as $h): ?>
                <?php $critico = strpos((string) $h['descripcion'], 'No cumple') === 0; ?>
                <div class="hallazgo-item <?php echo $critico ? 'hallazgo-critico' : ''; ?>">
                    <div class="hallazgo-categoria">Categoría <?php echo (int) $h['categoria']; ?> · Pregunta <?php echo (int) $h['pregunta_id']; ?></div>
                    <p><?php echo nl2br(e($h['descripcion'])); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="resumen-grid">
        <?php if (trim((string) $conclusiones['conclusiones']) !== ''): ?>
            <div class="card">
                <h2>Conclusiones</h2>
                <p><?php echo nl2br(e($conclusiones['conclusiones'])); ?></p>
            </div>
        <?php endif; ?>

        <?php if (trim((string) $conclusiones['recomendaciones']) !== ''): ?>
            <div class="card">
                <h2>Recomendaciones</h2>
                <p><?php echo nl2br(e($conclusiones['recomendaciones'])); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <div class="btn-group">
        <a href="index.php" class="btn btn-outline">Nueva simulación</a>
        <button id="btn-pdf" type="button" class="btn btn-success" onclick="generarPDF(<?php echo $evaluacionId; ?>)">Generar informe PDF</button>
    </div>
</div>

<script>
async function generarPDF(evaluacionId) {
    const btn = document.getElementById('btn-pdf');
    const original = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Generando PDF...';

    try {
        const response = await fetch('generar_reporte.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ evaluacion_id: evaluacionId })
        });

        if (!response.ok) {
            const mensaje = await response.text();
            throw new Error(mensaje || `Error HTTP ${response.status}`);
        }

        const tipo = response.headers.get('content-type') || '';
        if (!tipo.includes('application/pdf')) {
            throw new Error('El servidor no devolvió un archivo PDF.');
        }

        const blob = await response.blob();
        const url = URL.createObjectURL(blob);
        const enlace = document.createElement('a');
        enlace.href = url;
        enlace.download = `informe_simulador_lopdp_${evaluacionId}.pdf`;
        document.body.appendChild(enlace);
        enlace.click();
        enlace.remove();
        URL.revokeObjectURL(url);
    } catch (error) {
        console.error(error);
        alert('No se pudo generar el informe: ' + error.message);
    } finally {
        btn.disabled = false;
        btn.textContent = original;
    }
}
</script>
</body>
</html>
