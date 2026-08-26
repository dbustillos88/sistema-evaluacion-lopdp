<?php
require_once 'config/conexion.php';
$evaluacionId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$evaluacionId || $evaluacionId < 1) { header('Location: index.php'); exit; }
$evaluacion = obtenerEvaluacion($evaluacionId);
if (!$evaluacion) { http_response_code(404); exit('Simulación no encontrada.'); }
$respuestas = obtenerRespuestas($evaluacionId);
$hallazgos = obtenerHallazgos($evaluacionId);
$conclusiones = obtenerConclusiones($evaluacionId) ?? ['conclusiones'=>'','recomendaciones'=>''];
$metricas = calcularMetricasDesdeRespuestas($respuestas);
function e(?string $v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function pct(float $v): string { return number_format($v, 2, ',', '.') . '%'; }
function nivelClase(string $n): string { return $n==='Alto'?'estado-alto':($n==='Medio'?'estado-medio':'estado-bajo'); }
$colors = [1=>'#2563eb',2=>'#16a34a',3=>'#7c3aed'];
$estados = $metricas['estados'];
$totalEstados = array_sum($estados);
$pc = $totalEstados ? $estados['Cumple totalmente']/$totalEstados*100 : 0;
$pp = $totalEstados ? $estados['Cumple parcialmente']/$totalEstados*100 : 0;
$pn = $totalEstados ? $estados['No cumple']/$totalEstados*100 : 0;
$p2=$pc+$pp; $p3=$p2+$pn;
$donut = $totalEstados ? "conic-gradient(#16a34a 0 {$pc}%,#f59e0b {$pc}% {$p2}%,#ef4444 {$p2}% {$p3}%,#94a3b8 {$p3}% 100%)" : '#e5e7eb';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard #<?php echo $evaluacionId; ?> - Simulador LOPDP</title>
<link rel="stylesheet" href="css/estilos.css">
</head>
<body>
<div class="container">
  <header class="header">
    <div>
      <div class="eyebrow">Resultado guardado en base de datos</div>
      <h1>Dashboard de Cumplimiento LOPDP</h1>
      <p class="subtitle">Simulación #<?php echo $evaluacionId; ?> · <?php echo e($evaluacion['nombre_sistema']); ?></p>
    </div>
    <span class="estado-badge <?php echo nivelClase($metricas['nivel']); ?>">Nivel <?php echo e($metricas['nivel']); ?> · <?php echo pct($metricas['promedio_general']); ?></span>
  </header>

  <?php if (isset($_GET['success'])): ?><div class="mensaje-exito">La simulación se guardó correctamente. Este dashboard fue recalculado desde MySQL.</div><?php endif; ?>

  <section class="card section-card accent-blue">
    <div class="section-heading"><div><h2>Información de la simulación</h2></div><p>Datos registrados para identificar el análisis.</p></div>
    <table class="tabla-resultados">
      <tr><th>Institución</th><td><?php echo e($evaluacion['nombre_institucion']); ?></td></tr>
      <tr><th>RUC / Identificación</th><td><?php echo e($evaluacion['ruc'] ?: 'No registrado'); ?></td></tr>
      <tr><th>Sistema analizado</th><td><?php echo e($evaluacion['nombre_sistema']); ?></td></tr>
      <tr><th>Fecha</th><td><?php echo date('d/m/Y', strtotime($evaluacion['fecha_evaluacion'])); ?></td></tr>
      <tr><th>Responsable</th><td><?php echo e($evaluacion['evaluador']); ?></td></tr>
    </table>
  </section>

  <section class="score-grid">
    <?php foreach ($metricas['categorias'] as $id=>$cat): $c=$colors[$id]; ?>
      <article class="score-card <?php echo $id===2?'score-green':($id===3?'score-purple':'score-blue'); ?>">
        <div class="score-ring small" style="--ring:<?php echo $c; ?>;background:conic-gradient(<?php echo $c; ?> 0 <?php echo max(0,min(100,$cat['porcentaje'])); ?>%,#e5e7eb <?php echo max(0,min(100,$cat['porcentaje'])); ?>% 100%)">
          <div class="score-ring-inner"><strong><?php echo pct($cat['porcentaje']); ?></strong><span>Categoría <?php echo (int)$id; ?></span></div>
        </div>
        <h3><?php echo e($cat['nombre']); ?></h3>
        <p><?php echo (int)$cat['total']; ?> preguntas evaluadas</p>
      </article>
    <?php endforeach; ?>
    <article class="score-card score-dark">
      <div class="score-ring small" style="--ring:#06b6d4;background:conic-gradient(#06b6d4 0 <?php echo max(0,min(100,$metricas['promedio_general'])); ?>%,#e5e7eb <?php echo max(0,min(100,$metricas['promedio_general'])); ?>% 100%)">
        <div class="score-ring-inner"><strong><?php echo pct($metricas['promedio_general']); ?></strong><span>Promedio</span></div>
      </div>
      <h3>Nivel <?php echo e($metricas['nivel']); ?></h3>
      <p><?php echo (int)$metricas['total_preguntas']; ?> respuestas registradas</p>
    </article>
  </section>

  <section class="chart-container">
    <div class="chart-box">
      <h3>Avance de cumplimiento por categoría</h3>
      <div class="bar-chart">
      <?php $barClass=[1=>'bar-primary',2=>'bar-success',3=>'bar-warning']; foreach($metricas['categorias'] as $id=>$cat): $v=max(0,min(100,(float)$cat['porcentaje'])); ?>
        <div class="bar-item <?php echo $barClass[$id]; ?>"><span class="bar-label"><?php echo e($cat['nombre']); ?></span><div class="bar-track"><div class="bar-fill" style="width:<?php echo $v; ?>%"></div></div><span class="bar-percent"><?php echo pct($v); ?></span></div>
      <?php endforeach; ?>
      </div>
    </div>
    <div class="chart-box">
      <h3>Distribución de respuestas</h3>
      <div class="donut-wrap"><div class="donut-chart" style="background:<?php echo e($donut); ?>"><div class="donut-center"><strong><?php echo $totalEstados; ?></strong><span>respuestas</span></div></div></div>
      <div class="metrics-grid">
        <div class="metric-item"><span class="metric-dot dot-green"></span><span class="metric-text">Cumple totalmente</span><strong><?php echo $estados['Cumple totalmente']; ?></strong></div>
        <div class="metric-item"><span class="metric-dot dot-amber"></span><span class="metric-text">Cumple parcialmente</span><strong><?php echo $estados['Cumple parcialmente']; ?></strong></div>
        <div class="metric-item"><span class="metric-dot dot-red"></span><span class="metric-text">No cumple</span><strong><?php echo $estados['No cumple']; ?></strong></div>
        <div class="metric-item"><span class="metric-dot dot-gray"></span><span class="metric-text">No aplica</span><strong><?php echo $estados['No aplica']; ?></strong></div>
      </div>
    </div>
  </section>

  <div class="metodologia-nota"><strong>Metodología:</strong> “Cumple totalmente” aporta el 100% del peso, “Cumple parcialmente” el 50%, “No cumple” el 0% y “No aplica” se excluye del denominador.</div>

  <?php if ($hallazgos): ?>
  <section class="card section-card accent-amber" style="margin-top:16px">
    <div class="section-heading"><div><h2>Hallazgos identificados</h2></div><p>Se mantienen en el sistema para apoyar el análisis. No se imprimen en el PDF final.</p></div>
    <?php foreach($hallazgos as $h): $critico=strpos((string)$h['descripcion'],'No cumple')===0; ?>
      <article class="hallazgo-item <?php echo $critico?'hallazgo-critico':''; ?>"><div class="hallazgo-categoria">Categoría <?php echo (int)$h['categoria']; ?> · Pregunta <?php echo (int)$h['pregunta_id']; ?></div><p><?php echo nl2br(e($h['descripcion'])); ?></p></article>
    <?php endforeach; ?>
  </section>
  <?php endif; ?>

  <div class="resumen-grid">
    <?php if(trim((string)$conclusiones['conclusiones'])!==''): ?><section class="card section-card accent-blue"><h2>Conclusiones</h2><p><?php echo nl2br(e($conclusiones['conclusiones'])); ?></p></section><?php endif; ?>
    <?php if(trim((string)$conclusiones['recomendaciones'])!==''): ?><section class="card section-card accent-purple"><h2>Recomendaciones</h2><p><?php echo nl2br(e($conclusiones['recomendaciones'])); ?></p></section><?php endif; ?>
  </div>

  <div class="btn-group">
    <a href="index.php" class="btn btn-secondary">Nueva simulación</a>
    <button id="btn-pdf" type="button" class="btn btn-success" onclick="generarPDF(<?php echo $evaluacionId; ?>)">Generar informe PDF</button>
  </div>
</div>
<script>
async function generarPDF(evaluacionId){const btn=document.getElementById('btn-pdf'),original=btn.textContent;btn.disabled=true;btn.textContent='Generando PDF...';try{const r=await fetch('generar_reporte.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({evaluacion_id:evaluacionId})});if(!r.ok)throw new Error(await r.text()||`Error HTTP ${r.status}`);const blob=await r.blob(),url=URL.createObjectURL(blob),a=document.createElement('a');a.href=url;a.download=`informe_simulador_lopdp_${evaluacionId}.pdf`;document.body.appendChild(a);a.click();a.remove();URL.revokeObjectURL(url)}catch(e){alert('No se pudo generar el informe: '+e.message)}finally{btn.disabled=false;btn.textContent=original}}
</script>
</body></html>
