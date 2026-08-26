<?php
// =============================================
// SISTEMA DE EVALUACIÓN DE CUMPLIMIENTO LOPDP
// PÁGINA PRINCIPAL - VERSIÓN MEJORADA
// =============================================
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulador LOPDP - Sistema Biométrico</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
<div class="container">
    <!-- HEADER PREMIUM -->
    <div class="header">
        <div class="header-content">
            <div>
                <h1>Simulador de Cumplimiento LOPDP</h1>
                <p class="subtitle">Sistema de control de acceso biométrico · Proyecto de titulación de Desarrollo de Software</p>
            </div>
            <div class="badge">Instituto Tecnológico Universitario ISMAC</div>
        </div>
    </div>

    <?php if (!empty($_SESSION['error_formulario'])): ?>
        <div class="mensaje-error">
            <?php echo htmlspecialchars($_SESSION['error_formulario'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_formulario']); ?>
        </div>
    <?php endif; ?>

    <!-- NAVEGACIÓN DEL SIMULADOR -->
    <div class="tabs" role="tablist" aria-label="Secciones del simulador">
        <button class="tab activo" data-tab="tab-general" onclick="mostrarTab('tab-general')" type="button">
            <span class="tab-number">1</span> Información general
        </button>
        <button class="tab" data-tab="tab-evaluacion" onclick="mostrarTab('tab-evaluacion')" type="button">
            <span class="tab-number">2</span> Simulador
        </button>
        <button class="tab" data-tab="tab-hallazgos" onclick="mostrarTab('tab-hallazgos')" type="button">
            <span class="tab-number">3</span> Hallazgos
        </button>
        <button class="tab" data-tab="tab-conclusiones" onclick="mostrarTab('tab-conclusiones')" type="button">
            <span class="tab-number">4</span> Conclusiones
        </button>
        <button class="tab" data-tab="tab-recomendaciones" onclick="mostrarTab('tab-recomendaciones')" type="button">
            <span class="tab-number">5</span> Recomendaciones
        </button>
        <button class="tab" data-tab="tab-dashboard" onclick="mostrarTab('tab-dashboard')" type="button">
            <span class="tab-number">6</span> Dashboard
        </button>
        <button class="tab" data-tab="tab-acerca" onclick="mostrarTab('tab-acerca')" type="button">
            <span class="tab-number">7</span> Acerca del simulador
        </button>
    </div>

    <form id="form-evaluacion" action="guardar_evaluacion.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
        <!-- ========================================== -->
        <!-- TAB 1: INFORMACIÓN GENERAL -->
        <!-- ========================================== -->
        <div id="tab-general" class="tab-contenido activo">
            <div class="card">
                <h2>
                    Información general del simulador
                </h2>
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre_institucion">Nombre de la institución *</label>
                        <input type="text" id="nombre_institucion" name="nombre_institucion" 
                               value="Instituto Tecnológico Universitario ISMAC" required>
                    </div>
                    <div class="form-group">
                        <label for="ruc">RUC / Identificación <span class="campo-opcional">(Opcional)</span></label>
                        <input type="text" id="ruc" name="ruc" placeholder="Opcional - no obligatorio">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre_sistema">Sistema analizado *</label>
                        <input type="text" id="nombre_sistema" name="nombre_sistema" 
                               value="Sistema Biométrico de Control de Acceso" required>
                    </div>
                    <div class="form-group">
                        <label for="fecha_evaluacion">Fecha de simulación *</label>
                        <input type="date" id="fecha_evaluacion" name="fecha_evaluacion" 
                               value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="evaluador">Responsable de la simulación *</label>
                    <input type="text" id="evaluador" name="evaluador" 
                           value="David Fernando Bustillos Rosas" required>
                </div>
                <div class="btn-grupo">
                    <button type="button" class="btn btn-primario" onclick="mostrarTab('tab-evaluacion')">
                        Siguiente → Simulador
                    </button>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 2: EVALUACIÓN -->
        <!-- ========================================== -->
        <div id="tab-evaluacion" class="tab-contenido">
            <div class="card">
                <h2>
                    Simulador de cumplimiento normativo
                </h2>
                <p style="color: var(--gray-500); margin-bottom: 24px;">
                    El simulador está dividido en tres categorías. Cada pregunta permite asignar una ponderación, registrar la evidencia encontrada y seleccionar el nivel de cumplimiento.
                </p>

                <!-- ===== CATEGORÍA 1: ACTUALIZADA ===== -->
                <h3 class="categoria-titulo"><span>Categoría 1</span> Políticas institucionales de seguridad y protección de datos personales</h3>
                <div id="categoria-1">
                    <?php
                    // PREGUNTAS ACTUALIZADAS - CATEGORÍA 1 (7 preguntas)
                    $preguntas_cat1 = [
                        "¿Cuenta la institución con políticas de seguridad de la información que cumplan con lo dispuesto en la Ley de Protección de Datos Personales?",
                        "¿Tiene alguna estructura o designación formal el área de protección de datos personales (ej. Delegado/Oficial de Protección de Datos)?",
                        "¿Existe el consentimiento previo, explícito e informado de los estudiantes y docentes antes de capturar datos sensibles, como por ejemplo los biométricos?",
                        "¿Se cuenta con cláusulas o avisos de privacidad visibles e informativos al momento de recolectar cualquier tipo de dato personal?",
                        "¿Existe algún procedimiento para los estudiantes y personal que quieran ejercer sus derechos ARCO (Acceso, Rectificación, Cancelación y Oposición) y de retención de datos?",
                        "¿Están definidos los canales oficiales (físicos o digitales) para atenciones de solicitudes ARCO?",
                        "¿Existen medidas de seguridad técnicas (cifrado, control de acceso basado en roles) para proteger las bases de datos que contienen datos biométricos y personales?"
                    ];
                    
                    foreach ($preguntas_cat1 as $index => $texto):
                        $pregunta_id = $index + 1;
                    ?>
                    <div class="pregunta-item">
                        <div class="pregunta-texto"><?php echo $pregunta_id; ?>. <?php echo $texto; ?></div>
                        <div class="pregunta-controles">
                            <div>
                                <label>Ponderación (%)</label>
                                <input type="number" class="input-porcentaje" 
                                       name="cat1_peso_<?php echo $pregunta_id; ?>" 
                                       value="<?php echo $pregunta_id === 7 ? '14.26' : '14.29'; ?>" min="0" max="100" step="0.01">
                            </div>
                            <div>
                                <label>Estado de Cumplimiento</label>
                                <select class="select-estado" name="cat1_estado_<?php echo $pregunta_id; ?>">
                                    <option value="Cumple totalmente">Cumple totalmente</option>
                                    <option value="Cumple parcialmente" selected>Cumple parcialmente</option>
                                    <option value="No cumple">No cumple</option>
                                    <option value="No aplica">No aplica</option>
                                </select>
                            </div>
                            <div>
                                <label>Evidencia / Observación</label>
                                <input type="text" class="input-observacion" 
                                       name="cat1_observacion_<?php echo $pregunta_id; ?>" 
                                       placeholder="Registre la evidencia encontrada" style="width:100%;">
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="peso-total">Ponderación categoría 1: <strong id="peso-total-cat1">100.00%</strong></div>

                <!-- ===== CATEGORÍA 2 ===== -->
                <h3 class="categoria-titulo"><span>Categoría 2</span> Sistema de acceso biométrico: ciberseguridad, flujo y gestión de la información</h3>
                <div id="categoria-2">
                    <?php
                    // PREGUNTAS ACTUALIZADAS - CATEGORÍA 2 (15 preguntas)
                    $preguntas_cat2 = [
                        "¿El sistema web instalado cuenta con un sistema de seguridad robusto?",
                        "¿Los dispositivos de captura (sensores biométricos) cuentan con cifrado de la información de las huellas y rostros capturados?",
                        "¿Los dispositivos de captura (sensores biométricos) cuentan con un sistema o están conectados a un UPS?",
                        "¿El sistema permite modos alternativos de acceso autorizados en caso de fallo técnico de la biometría?",
                        "¿El sistema convierte las características biométricas en representaciones matemáticas (templates/hashes) incompletas no reversibles?",
                        "¿Las imágenes biométricas originales (rostros o huellas brutas) son eliminadas inmediatamente tras generar el vector de características?",
                        "¿La base de datos donde se almacenan los patrones biométricos cuenta con algoritmos de cifrado robustos?",
                        "¿Las transmisiones de datos entre los dispositivos biométricos de los laboratorios y el servidor se realizan mediante canales cifrados?",
                        "¿Existe consentimiento previo, expreso e informado (físico o digital) antes del registro biométrico de estudiantes y docentes de Software?",
                        "¿Se dispone de un mecanismo accesible para que el estudiante/docente pueda revocar su consentimiento u opositarse al uso biométrico?",
                        "¿Existe una alternativa equivalente no biométrica (ej. tarjeta NFC, clave personal) para el acceso a aulas y laboratorios?",
                        "¿El sistema registra trazabilidad completa de cada intento de acceso, incluyendo fecha, hora, usuario y resultado?",
                        "¿El acceso a la consola de administración del sistema biométrico está restringido únicamente a personal técnico autorizado?",
                        "¿Existen procedimientos de depuración automática para eliminar registros biométricos de estudiantes que se retiran o gradúan?",
                        "¿Existen respaldos periódicos y seguros de la base de datos del sistema biométrico?"
                    ];
                    
                    foreach ($preguntas_cat2 as $index => $texto):
                        $pregunta_id = $index + 1;
                    ?>
                    <div class="pregunta-item">
                        <div class="pregunta-texto"><?php echo $pregunta_id; ?>. <?php echo $texto; ?></div>
                        <div class="pregunta-controles">
                            <div>
                                <label>Ponderación (%)</label>
                                <input type="number" class="input-porcentaje" 
                                       name="cat2_peso_<?php echo $pregunta_id; ?>" 
                                       value="<?php echo $pregunta_id === 15 ? '6.62' : '6.67'; ?>" min="0" max="100" step="0.01">
                            </div>
                            <div>
                                <label>Estado de Cumplimiento</label>
                                <select class="select-estado" name="cat2_estado_<?php echo $pregunta_id; ?>">
                                    <option value="Cumple totalmente">Cumple totalmente</option>
                                    <option value="Cumple parcialmente" selected>Cumple parcialmente</option>
                                    <option value="No cumple">No cumple</option>
                                    <option value="No aplica">No aplica</option>
                                </select>
                            </div>
                            <div>
                                <label>Evidencia / Observación</label>
                                <input type="text" class="input-observacion" 
                                       name="cat2_observacion_<?php echo $pregunta_id; ?>" 
                                       placeholder="Registre la evidencia encontrada" style="width:100%;">
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="peso-total">Ponderación categoría 2: <strong id="peso-total-cat2">100.00%</strong></div>

                <!-- ===== CATEGORÍA 3: ACTORES DEL SISTEMA (SOLO 3 PREGUNTAS) ===== -->
<h3 class="categoria-titulo"><span>Categoría 3</span> Actores que forman parte del sistema: usuarios y operadores</h3>
<div id="categoria-3">
    <?php
    // SOLO 3 PREGUNTAS - CATEGORÍA 3
    $preguntas_cat3 = [
        "¿Se ha realizado una Evaluación de Impacto en la Protección de Datos (EIPD) antes de implementar tecnologías de control de acceso biométrico?",
        "¿La comunidad institucional recibe capacitaciones periódicas acerca de la normativa y las políticas de seguridad y protección de datos personales?",
        "¿Los operarios y desarrolladores del sistema biométrico han sido capacitados en el tratamiento de datos personales sensibles y en los procedimientos institucionales aplicables?"
    ];
    
    foreach ($preguntas_cat3 as $index => $texto):
        $pregunta_id = $index + 1;
    ?>
    <div class="pregunta-item">
        <div class="pregunta-texto"><?php echo $pregunta_id; ?>. <?php echo $texto; ?></div>
        <div class="pregunta-controles">
            <div>
                <label>Ponderación (%)</label>
                <input type="number" class="input-porcentaje" 
                       name="cat3_peso_<?php echo $pregunta_id; ?>" 
                       value="<?php echo $pregunta_id === 3 ? '33.34' : '33.33'; ?>" min="0" max="100" step="0.01">
            </div>
            <div>
                <label>Estado de Cumplimiento</label>
                <select class="select-estado" name="cat3_estado_<?php echo $pregunta_id; ?>">
                    <option value="Cumple totalmente">Cumple totalmente</option>
                    <option value="Cumple parcialmente" selected>Cumple parcialmente</option>
                    <option value="No cumple">No cumple</option>
                    <option value="No aplica">No aplica</option>
                </select>
            </div>
            <div>
                <label>Evidencia / Observación</label>
                <input type="text" class="input-observacion" 
                       name="cat3_observacion_<?php echo $pregunta_id; ?>" 
                       placeholder="Registre la evidencia encontrada" style="width:100%;">
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<div class="peso-total">Ponderación categoría 3: <strong id="peso-total-cat3">100.00%</strong></div>
                <div class="btn-grupo" style="justify-content: space-between;">
                    <button type="button" class="btn btn-advertencia" onclick="mostrarTab('tab-general')">← Anterior</button>
                    <button type="button" class="btn btn-primario" onclick="generarHallazgos(); mostrarTab('tab-hallazgos');">Siguiente → Hallazgos</button>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 3: HALLAZGOS -->
        <!-- ========================================== -->
        <div id="tab-hallazgos" class="tab-contenido">
            <div class="card">
                <h2>
                    Hallazgos identificados
                </h2>
                <p style="color: var(--gray-500); margin-bottom: 20px;">
                    Este módulo presenta los requisitos en los que se detectó cumplimiento parcial o incumplimiento, 
                    junto con la evidencia registrada durante la simulación.
                </p>
                <div id="hallazgos-container">
                    <p style="color: #94A3B8;">Complete el simulador para generar los hallazgos automáticamente.</p>
                </div>
                <div class="btn-grupo" style="justify-content: space-between; margin-top: 20px;">
                    <button type="button" class="btn btn-advertencia" onclick="mostrarTab('tab-evaluacion')">← Simulador</button>
                    <button type="button" class="btn btn-primario" onclick="mostrarTab('tab-conclusiones')">Siguiente → Conclusiones</button>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 4: CONCLUSIONES -->
        <!-- ========================================== -->
        <div id="tab-conclusiones" class="tab-contenido">
            <div class="card">
                <h2>
                    Conclusiones
                </h2>
                <div class="form-group">
                    <label for="conclusiones">Conclusiones</label>
                    <textarea id="conclusiones" name="conclusiones" rows="8" 
                              placeholder="Ej: El sistema de control de acceso biométrico demuestra un nivel elevado de cumplimiento técnico..."></textarea>
                </div>
                <div class="btn-grupo" style="justify-content: space-between;">
                    <button type="button" class="btn btn-advertencia" onclick="mostrarTab('tab-hallazgos')">← Hallazgos</button>
                    <button type="button" class="btn btn-primario" onclick="mostrarTab('tab-recomendaciones')">Siguiente → Recomendaciones</button>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 5: RECOMENDACIONES -->
        <!-- ========================================== -->
        <div id="tab-recomendaciones" class="tab-contenido">
            <div class="card">
                <h2>
                    Recomendaciones
                </h2>
                <div class="form-group">
                    <label for="recomendaciones">Recomendaciones</label>
                    <textarea id="recomendaciones" name="recomendaciones" rows="8" 
                              placeholder="Ej: Se recomienda migrar el almacenamiento de imágenes faciales hacia vectores matemáticos irreversibles..."></textarea>
                </div>
                <div class="btn-grupo" style="justify-content: space-between;">
                    <button type="button" class="btn btn-advertencia" onclick="mostrarTab('tab-conclusiones')">← Conclusiones</button>
                    <button type="button" class="btn btn-exito" onclick="mostrarTab('tab-dashboard')">Ver Dashboard →</button>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
<!-- TAB 6: DASHBOARD DE RESULTADOS -->
<!-- ========================================== -->
<div id="tab-dashboard" class="tab-contenido">
    <div class="card">
        <h2>Dashboard de resultados</h2>
        <p style="color: var(--gray-500); margin-bottom: 24px;">
            Resumen gráfico de los resultados del simulador. Los indicadores se actualizan en tiempo real.
        </p>

        <!-- ========== TARJETAS KPI ========== -->
        <div class="dashboard-grid">
            <div class="dashboard-card">
                <div class="label">Políticas institucionales</div>
                <div id="porcentaje-cat1" class="value color-verde">0%</div>
                <div class="sub">Cumplimiento normativo</div>
                <div class="barra-progreso barra-verde">
                    <div id="barra-cat1" class="barra" style="width:0%;"></div>
                </div>
            </div>
            
            <div class="dashboard-card">
                <div class="label">Sistema biométrico</div>
                <div id="porcentaje-cat2" class="value color-verde">0%</div>
                <div class="sub">Ciberseguridad y gestión</div>
                <div class="barra-progreso barra-verde">
                    <div id="barra-cat2" class="barra" style="width:0%;"></div>
                </div>
            </div>
            
            <div class="dashboard-card">
                <div class="label">Actores del sistema</div>
                <div id="porcentaje-cat3" class="value color-verde">0%</div>
                <div class="sub">Capacitación y conocimiento</div>
                <div class="barra-progreso barra-verde">
                    <div id="barra-cat3" class="barra" style="width:0%;"></div>
                </div>
            </div>
            
            <div class="dashboard-card dashboard-card-destacado">
                <div class="label">Promedio general</div>
                <div id="promedio-general" class="value">0%</div>
                <div class="sub">Total de preguntas: <span id="total-preguntas">0</span> · Nivel: <span id="nivel-general">Bajo</span></div>
                
            </div>
        </div>

        <!-- ========== GRÁFICOS DETALLADOS ========== -->
        <div class="chart-container">
            <div class="chart-box">
                <h3>Nivel de cumplimiento por categoría</h3>
                <div class="bar-chart chart-fallback">
                    <div class="bar-item bar-primary">
                        <span class="bar-label">Políticas institucionales</span>
                        <div class="bar-track">
                            <div id="barra-detalle-cat1" class="bar-fill" style="width:0%;">0%</div>
                        </div>
                        <span class="bar-percent" id="detalle-porcentaje-cat1">0%</span>
                    </div>
                    <div class="bar-item bar-success">
                        <span class="bar-label">Sistema biométrico</span>
                        <div class="bar-track">
                            <div id="barra-detalle-cat2" class="bar-fill" style="width:0%;">0%</div>
                        </div>
                        <span class="bar-percent" id="detalle-porcentaje-cat2">0%</span>
                    </div>
                    <div class="bar-item bar-warning">
                        <span class="bar-label">Actores del sistema</span>
                        <div class="bar-track">
                            <div id="barra-detalle-cat3" class="bar-fill" style="width:0%;">0%</div>
                        </div>
                        <span class="bar-percent" id="detalle-porcentaje-cat3">0%</span>
                    </div>
                </div>
            </div>
            
            <div class="chart-box">
                <h3>Distribución de estados</h3>
                <div class="donut-wrap" aria-label="Distribución de estados de cumplimiento">
                    <div id="donut-estados" class="donut-chart">
                        <div class="donut-center"><strong id="donut-total">0</strong><span>respuestas</span></div>
                    </div>
                </div>
                <div class="metrics-grid">
                    <div class="metric-item">
                        <div class="metric-dot" style="background: #16a34a;"></div>
                        <span class="metric-text">Cumple totalmente</span>
                        <span class="metric-count" id="totales-cumple">0</span>
                    </div>
                    <div class="metric-item">
                        <div class="metric-dot" style="background: #d97706;"></div>
                        <span class="metric-text">Cumple parcialmente</span>
                        <span class="metric-count" id="totales-parcial">0</span>
                    </div>
                    <div class="metric-item">
                        <div class="metric-dot" style="background: #dc2626;"></div>
                        <span class="metric-text">No cumple</span>
                        <span class="metric-count" id="totales-no-cumple">0</span>
                    </div>
                    <div class="metric-item">
                        <div class="metric-dot" style="background: #94A3B8;"></div>
                        <span class="metric-text">No aplica</span>
                        <span class="metric-count" id="totales-no-aplica">0</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="metodologia-nota">
            <strong>Metodología:</strong> Cumple totalmente = 100% del peso; cumple parcialmente = 50%; no cumple = 0%; “No aplica” se excluye del denominador. El promedio general corresponde al promedio de las categorías aplicables.
        </div>

        <!-- ========== BOTONES ========== -->
        <div class="btn-group" style="justify-content: space-between; margin-top: 24px;">
            <button type="button" class="btn btn-warning" onclick="mostrarTab('tab-recomendaciones')">← Recomendaciones</button>
            <div class="acciones-finales">
                <button type="submit" class="btn btn-success" onclick="return confirm('¿Desea guardar esta simulación y abrir el dashboard final?')">
                    Guardar simulación
                </button>
                <span class="ayuda-accion">El informe PDF se genera desde el dashboard una vez guardados los datos.</span>
            </div>
        </div>
    </div>
</div>

        <!-- ========================================== -->
        <!-- TAB 7: ACERCA DEL SIMULADOR -->
        <!-- ========================================== -->
        <div id="tab-acerca" class="tab-contenido">
            <div class="card">
                <h2>Acerca del simulador</h2>
                <p class="intro-texto">
                    Este simulador fue desarrollado como parte de un proyecto de titulación de la carrera de Desarrollo de Software. Su objetivo es apoyar la revisión académica de un sistema de control de acceso biométrico frente a criterios relacionados con protección de datos personales.
                </p>

                <div class="info-grid">
                    <section class="info-bloque">
                        <h3>Base normativa</h3>
                        <p>La estructura del simulador toma como referencia la Ley Orgánica de Protección de Datos Personales del Ecuador (LOPDP) y su Reglamento General. Las preguntas se concentran en aspectos de consentimiento, transparencia, ejercicio de derechos, seguridad de la información y tratamiento de datos biométricos.</p>
                    </section>
                    <section class="info-bloque">
                        <h3>Qué analiza</h3>
                        <p>Se revisan tres grupos: políticas institucionales, controles del sistema biométrico y responsabilidades de los actores que utilizan u operan el sistema.</p>
                    </section>
                    <section class="info-bloque">
                        <h3>Cómo calcula el resultado</h3>
                        <p>Cada requisito tiene una ponderación. “Cumple totalmente” aporta el 100% del peso, “Cumple parcialmente” el 50%, “No cumple” el 0% y “No aplica” se excluye del cálculo. El dashboard presenta resultados por categoría y un promedio general.</p>
                    </section>
                    <section class="info-bloque">
                        <h3>Tecnologías utilizadas</h3>
                        <p>El sistema está desarrollado con PHP, MySQL, JavaScript, HTML y CSS. Los informes se generan en PDF mediante TCPDF.</p>
                    </section>
                </div>

                <div class="aviso-academico">
                    <strong>Alcance académico:</strong> el resultado es una referencia para análisis y documentación del proyecto. No reemplaza una auditoría jurídica, técnica o de seguridad realizada por profesionales especializados.
                </div>

                <div class="btn-grupo" style="justify-content: space-between;">
                    <button type="button" class="btn btn-advertencia" onclick="mostrarTab('tab-dashboard')">← Dashboard</button>
                    <button type="button" class="btn btn-primario" onclick="mostrarTab('tab-general')">Volver al inicio</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="js/funciones.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        actualizarDashboard();
    });
</script>
</body>
</html>