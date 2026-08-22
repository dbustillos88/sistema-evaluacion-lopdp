<?php
// =============================================
// DASHBOARD DE RESULTADOS CON PDF REAL
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="container">
    <div class="header">
        <h1>📊 Dashboard de Resultados</h1>
        <p>Evaluación del sistema biométrico - Carrera de Desarrollo de Software</p>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div style="background:#ECFDF5;padding:16px 20px;border-radius:12px;margin-bottom:20px;border-left:4px solid #10B981;">
            ✅ Evaluación guardada exitosamente. ID: #<?php echo $evaluacion_id; ?>
        </div>
    <?php endif; ?>

    <!-- INFORMACIÓN GENERAL -->
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

    <!-- RESULTADOS -->
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

    <!-- HALLAZGOS -->
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

    <!-- CONCLUSIONES -->
    <?php if ($conclusiones): ?>
    <div class="card">
        <h2>📄 Conclusiones</h2>
        <p><?php echo nl2br(htmlspecialchars($conclusiones['conclusiones'])); ?></p>
    </div>
    <?php endif; ?>

    <!-- RECOMENDACIONES -->
    <?php if ($conclusiones && $conclusiones['recomendaciones']): ?>
    <div class="card">
        <h2>💡 Recomendaciones</h2>
        <p><?php echo nl2br(htmlspecialchars($conclusiones['recomendaciones'])); ?></p>
    </div>
    <?php endif; ?>

    <!-- BOTONES ACTUALIZADOS -->
    <div class="btn-grupo" style="margin-top:20px;">
        <a href="index.php" class="btn btn-primario">⬅ Nueva Evaluación</a>
        <button onclick="generarPDFDesdeDashboard(<?php echo $evaluacion_id; ?>)" class="btn btn-exito">
            📄 Generar PDF con Datos
        </button>
    </div>
</div>

<script>
// =============================================
// GENERAR PDF CON LOS DATOS DEL DASHBOARD
// =============================================
function generarPDFDesdeDashboard(evaluacionId) {
    // Mostrar loading
    const btn = document.querySelector('.btn-exito');
    const textoOriginal = btn.textContent;
    btn.textContent = '⏳ Generando PDF...';
    btn.disabled = true;
    
    // Obtener datos de la página
    const datos = {
        evaluacion_id: evaluacionId,
        institucion: document.querySelector('.tabla-resultados tr:nth-child(1) td')?.textContent || 'No registrado',
        ruc: document.querySelector('.tabla-resultados tr:nth-child(2) td')?.textContent || 'No registrado',
        sistema: document.querySelector('.tabla-resultados tr:nth-child(3) td')?.textContent || 'No registrado',
        fecha: document.querySelector('.tabla-resultados tr:nth-child(4) td')?.textContent || 'No registrado',
        evaluador: document.querySelector('.tabla-resultados tr:nth-child(5) td')?.textContent || 'No registrado',
        cat1: document.querySelector('.dashboard-card:nth-child(1) .numero')?.textContent || '0%',
        cat2: document.querySelector('.dashboard-card:nth-child(2) .numero')?.textContent || '0%',
        cat3: document.querySelector('.dashboard-card:nth-child(3) .numero')?.textContent || '0%',
        conclusiones: document.querySelector('.card:nth-child(5) p')?.textContent || 'No hay conclusiones',
        recomendaciones: document.querySelector('.card:nth-child(6) p')?.textContent || 'No hay recomendaciones'
    };
    
    // Obtener hallazgos
    const hallazgosItems = document.querySelectorAll('.hallazgo-item p');
    datos.hallazgos = Array.from(hallazgosItems).map(el => el.textContent.trim());
    
    // Enviar al servidor
    fetch('generar_reporte_dashboard.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(datos)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en el servidor');
        }
        return response.blob();
    })
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'informe_evaluacion_lopdp_' + evaluacionId + '.pdf';
        document.body.appendChild(a);
        a.click();
        a.remove();
        window.URL.revokeObjectURL(url);
        
        btn.textContent = textoOriginal;
        btn.disabled = false;
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Error al generar el PDF');
        btn.textContent = textoOriginal;
        btn.disabled = false;
    });
}
</script>
</body>
</html>