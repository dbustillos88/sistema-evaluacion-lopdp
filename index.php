<?php
// =============================================
// SISTEMA DE EVALUACIÓN DE CUMPLIMIENTO LOPDP
// PÁGINA PRINCIPAL - VERSIÓN MEJORADA
// =============================================
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluación LOPDP - Sistema Biométrico</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="container">
    <!-- HEADER PREMIUM -->
    <div class="header">
        <div class="header-content">
            <div>
                <h1>📊 Evaluación de Cumplimiento LOPDP</h1>
                <p class="subtitle">Sistema de control de acceso biométrico - Carrera de Desarrollo de Software</p>
            </div>
            <div class="badge">
                <i class="fas fa-building"></i> Instituto Tecnológico Universitario ISMAC
            </div>
        </div>
    </div>

    <!-- TABS DE NAVEGACIÓN -->
    <div class="tabs">
        <button class="tab activo" data-tab="tab-general" onclick="mostrarTab('tab-general')">
            <span class="tab-icon">📋</span> 1. Información General
        </button>
        <button class="tab" data-tab="tab-evaluacion" onclick="mostrarTab('tab-evaluacion')">
            <span class="tab-icon">📝</span> 2. Evaluación
        </button>
        <button class="tab" data-tab="tab-hallazgos" onclick="mostrarTab('tab-hallazgos')">
            <span class="tab-icon">🔍</span> 3. Hallazgos
        </button>
        <button class="tab" data-tab="tab-conclusiones" onclick="mostrarTab('tab-conclusiones')">
            <span class="tab-icon">📄</span> 4. Conclusiones
        </button>
        <button class="tab" data-tab="tab-recomendaciones" onclick="mostrarTab('tab-recomendaciones')">
            <span class="tab-icon">💡</span> 5. Recomendaciones
        </button>
        <button class="tab" data-tab="tab-dashboard" onclick="mostrarTab('tab-dashboard')">
            <span class="tab-icon">📊</span> 6. Dashboard
        </button>
    </div>

    <form id="form-evaluacion" action="guardar_evaluacion.php" method="POST">
        <!-- ========================================== -->
        <!-- TAB 1: INFORMACIÓN GENERAL -->
        <!-- ========================================== -->
        <div id="tab-general" class="tab-contenido activo">
            <div class="card">
                <h2>
                    <span class="card-icon">📋</span>
                    Información General de la Evaluación
                </h2>
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre_institucion">🏛️ Nombre de la Institución *</label>
                        <input type="text" id="nombre_institucion" name="nombre_institucion" 
                               value="Instituto Tecnológico Universitario ISMAC" required>
                    </div>
                    <div class="form-group">
                        <label for="ruc">📄 RUC / Identificación <span style="color:#94A3B8;font-size:0.8rem;">(Opcional)</span></label>
                        <input type="text" id="ruc" name="ruc" placeholder="Opcional - no obligatorio">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre_sistema">💻 Sistema Evaluado *</label>
                        <input type="text" id="nombre_sistema" name="nombre_sistema" 
                               value="Sistema Biométrico de Control de Acceso" required>
                    </div>
                    <div class="form-group">
                        <label for="fecha_evaluacion">📅 Fecha de Evaluación *</label>
                        <input type="date" id="fecha_evaluacion" name="fecha_evaluacion" 
                               value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="evaluador">👤 Evaluador *</label>
                    <input type="text" id="evaluador" name="evaluador" 
                           value="David Fernando Bustillos Rosas" required>
                </div>
                <div class="btn-grupo">
                    <button type="button" class="btn btn-primario" onclick="mostrarTab('tab-evaluacion')">
                        Siguiente → Evaluación <i class="fas fa-arrow-right"></i>
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
                    <span class="card-icon">📝</span>
                    Evaluación de Cumplimiento Normativo
                </h2>
                <p style="color: var(--gray-500); margin-bottom: 24px;">
                    <i class="fas fa-info-circle"></i> La evaluación se encuentra dividida en tres categorías. 
                    Cada pregunta permite ingresar el porcentaje de ponderación, registrar la evidencia encontrada 
                    y establecer el nivel de cumplimiento.
                </p>

                <!-- ===== CATEGORÍA 1: ACTUALIZADA ===== -->
                <h3 style="color: var(--gray-700); margin: 30px 0 20px 0; display:flex; align-items:center; gap:12px; padding:16px 20px; background: linear-gradient(135deg, #EEF2FF, #E0E7FF); border-radius: 12px;">
                    <span style="font-size:1.5rem;">📌</span> 
                    <span style="font-weight:700; color: var(--primary);">Categoría 1.</span> 
                    <span>Políticas institucionales de seguridad y protección de datos personales</span>
                </h3>
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
                                       value="14" min="0" max="100" step="0.5">
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

                <!-- ===== CATEGORÍA 2 ===== -->
                <h3 style="color: var(--gray-700); margin: 30px 0 20px 0; display:flex; align-items:center; gap:12px; padding:16px 20px; background: linear-gradient(135deg, #ECFDF5, #D1FAE5); border-radius: 12px;">
                    <span style="font-size:1.5rem;">🔐</span> 
                    <span style="font-weight:700; color: #059669;">Categoría 2.</span> 
                    <span>Sistema de acceso biométrico (ciberseguridad, flujo y gestión de la información)</span>
                </h3>
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
                                       value="7" min="0" max="100" step="0.5">
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

                <!-- ===== CATEGORÍA 3 ===== -->
                <h3 style="color: var(--gray-700); margin: 30px 0 20px 0; display:flex; align-items:center; gap:12px; padding:16px 20px; background: linear-gradient(135deg, #FEF3C7, #FDE68A); border-radius: 12px;">
                    <span style="font-size:1.5rem;">👥</span> 
                    <span style="font-weight:700; color: #B45309;">Categoría 3.</span> 
                    <span>Actores que forman parte del sistema (consumidores y operarios)</span>
                </h3>
                <div id="categoria-3">
                    <?php
                    // PREGUNTAS ACTUALIZADAS - CATEGORÍA 3 (8 preguntas)
                    $preguntas_cat3 = [
                        "¿Se ha realizado una Evaluación de Impacto en la Protección de Datos (EIPD) antes de implementar tecnologías de control de acceso biométrico?",
                        "¿La comunidad institucional recibe capacitaciones periódicas acerca de la normativa y las políticas de seguridad y protección de datos personales?",
                        "¿Los docentes que utilizan el sistema de acceso biométrico han recibido capacitación sobre las políticas institucionales de seguridad y protección de datos personales?",
                        "¿Los estudiantes usuarios del sistema conocen la finalidad para la cual se recopilan sus datos biométricos y los mecanismos disponibles para ejercer sus derechos?",
                        "¿Los operarios y desarrolladores del sistema biométrico han sido capacitados en el tratamiento de datos personales sensibles y en los procedimientos institucionales aplicables?",
                        "¿Existe evidencia de las capacitaciones realizadas, como registros de asistencia, material impartido o constancias de participación?",
                        "¿El personal autorizado conoce el procedimiento que debe seguir ante un incidente de seguridad o acceso no autorizado a datos personales?",
                        "¿Las capacitaciones sobre protección de datos y seguridad de la información se actualizan cuando cambian los procedimientos, sistemas o normativa aplicable?"
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
                                       value="12" min="0" max="100" step="0.5">
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
                    <span class="card-icon">🔍</span>
                    Hallazgos Identificados
                </h2>
                <p style="color: var(--gray-500); margin-bottom: 20px;">
                    Este módulo presenta los requisitos en los que se detectó cumplimiento parcial o incumplimiento, 
                    junto con la evidencia registrada durante la evaluación.
                </p>
                <div id="hallazgos-container">
                    <p style="color: #94A3B8;">Complete la evaluación para generar los hallazgos automáticamente.</p>
                </div>
                <div class="btn-grupo" style="justify-content: space-between; margin-top: 20px;">
                    <button type="button" class="btn btn-advertencia" onclick="mostrarTab('tab-evaluacion')">← Evaluación</button>
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
                    <span class="card-icon">📄</span>
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
                    <span class="card-icon">💡</span>
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
        <!-- TAB 6: DASHBOARD PREMIUM -->
        <!-- ========================================== -->
        <div id="tab-dashboard" class="tab-contenido">
            <div class="card">
                <h2>
                    <span class="card-icon">📊</span>
                    Dashboard de Resultados
                </h2>
                <p style="color: var(--gray-500); margin-bottom: 24px;">
                    El módulo final consolida los porcentajes obtenidos en las tres categorías. 
                    Los resultados se visualizan mediante un dashboard con gráficos de barras y animaciones.
                </p>

                <div class="dashboard-grid">
                    <!-- Categoría 1 -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3>🏛️ Políticas Institucionales</h3>
                            <div class="icon-circle azul">
                                <i class="fas fa-building"></i>
                            </div>
                        </div>
                        <div id="porcentaje-cat1" class="numero color-verde">0%</div>
                        <p class="etiqueta">Cumplimiento normativo institucional</p>
                        <div class="barra-progreso barra-verde">
                            <div id="barra-cat1" class="barra" style="width:0%;"></div>
                        </div>
                    </div>

                    <!-- Categoría 2 -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3>🔐 Sistema Biométrico</h3>
                            <div class="icon-circle verde">
                                <i class="fas fa-fingerprint"></i>
                            </div>
                        </div>
                        <div id="porcentaje-cat2" class="numero color-verde">0%</div>
                        <p class="etiqueta">Ciberseguridad y gestión de datos</p>
                        <div class="barra-progreso barra-verde">
                            <div id="barra-cat2" class="barra" style="width:0%;"></div>
                        </div>
                    </div>

                    <!-- Categoría 3 -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3>👥 Actores del Sistema</h3>
                            <div class="icon-circle naranja">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div id="porcentaje-cat3" class="numero color-verde">0%</div>
                        <p class="etiqueta">Capacitación y conocimiento</p>
                        <div class="barra-progreso barra-verde">
                            <div id="barra-cat3" class="barra" style="width:0%;"></div>
                        </div>
                    </div>
                </div>

                <div style="text-align: center; margin: 20px 0; padding: 16px; background: var(--gray-50); border-radius: var(--radius-sm);">
                    <p style="color: var(--gray-500); font-size: 0.9rem;">
                        <i class="fas fa-sync-alt" style="margin-right: 8px;"></i>
                        Los porcentajes se calculan automáticamente en base a la ponderación y estado de cada pregunta.
                        <br>
                        <span style="font-size: 0.8rem; color: var(--gray-400);">
                            ✅ Cumple totalmente (100%) | ⚠️ Cumple parcialmente (50%) | ❌ No cumple (0%)
                        </span>
                    </p>
                </div>

                <div class="btn-grupo" style="justify-content: space-between;">
                    <button type="button" class="btn btn-advertencia" onclick="mostrarTab('tab-recomendaciones')">← Recomendaciones</button>
                    <button type="submit" class="btn btn-exito" onclick="return confirm('¿Está seguro de guardar la evaluación?')">
                        💾 Guardar Evaluación
                    </button>
                    <button type="button" class="btn btn-primario" onclick="generarReporte()">
                        📄 Generar Reporte
                    </button>
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