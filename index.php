<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$categorias = [
    1 => [
        'titulo' => 'Políticas institucionales de seguridad y protección de datos personales',
        'corto' => 'Políticas institucionales',
        'preguntas' => [
            '¿Cuenta la institución con políticas de seguridad de la información que cumplan con lo dispuesto en la Ley Orgánica de Protección de Datos Personales?',
            '¿Tiene alguna estructura o designación formal el área de protección de datos personales, por ejemplo un Delegado u Oficial de Protección de Datos?',
            '¿Existe consentimiento previo, explícito e informado de estudiantes y docentes antes de capturar datos sensibles, como los biométricos?',
            '¿Se cuenta con cláusulas o avisos de privacidad visibles e informativos al momento de recolectar datos personales?',
            '¿Existe un procedimiento para que estudiantes y personal ejerzan sus derechos ARCO y se gestione la retención de datos?',
            '¿Están definidos canales oficiales, físicos o digitales, para atender solicitudes relacionadas con derechos ARCO?',
            '¿Existen medidas técnicas de seguridad, como cifrado y control de acceso por roles, para proteger bases de datos con información biométrica y personal?'
        ],
        'pesos' => [14.29, 14.29, 14.29, 14.29, 14.29, 14.29, 14.26],
    ],
    2 => [
        'titulo' => 'Sistema de acceso biométrico: ciberseguridad, flujo y gestión de la información',
        'corto' => 'Sistema biométrico',
        'preguntas' => [
            '¿El sistema web instalado cuenta con mecanismos de seguridad acordes con el tratamiento de datos personales?',
            '¿Los dispositivos de captura biométrica cuentan con cifrado para proteger la información de huellas y rostros?',
            '¿Los dispositivos de captura biométrica están protegidos ante interrupciones eléctricas mediante UPS u otro mecanismo equivalente?',
            '¿El sistema permite modos alternativos de acceso autorizados cuando falla la biometría?',
            '¿El sistema convierte las características biométricas en representaciones matemáticas no reversibles?',
            '¿Las imágenes biométricas originales son eliminadas después de generar el vector o plantilla de características?',
            '¿La base de datos que almacena patrones biométricos utiliza algoritmos de cifrado robustos?',
            '¿Las transmisiones entre dispositivos biométricos y servidor se realizan mediante canales cifrados?',
            '¿Existe consentimiento previo, expreso e informado antes del registro biométrico de estudiantes y docentes?',
            '¿Existe un mecanismo accesible para revocar el consentimiento u oponerse al uso de datos biométricos?',
            '¿Existe una alternativa equivalente no biométrica para acceder a aulas o laboratorios?',
            '¿El sistema registra trazabilidad de cada intento de acceso, incluyendo fecha, hora, usuario y resultado?',
            '¿El acceso a la consola administrativa está restringido al personal técnico autorizado?',
            '¿Existen procedimientos para depurar registros biométricos de estudiantes que se retiran o gradúan?',
            '¿Existen respaldos periódicos y seguros de la base de datos del sistema biométrico?'
        ],
        'pesos' => [6.67, 6.67, 6.67, 6.67, 6.67, 6.67, 6.67, 6.67, 6.67, 6.67, 6.67, 6.67, 6.67, 6.67, 6.62],
    ],
    3 => [
        'titulo' => 'Actores que forman parte del sistema: usuarios y operadores',
        'corto' => 'Actores del sistema',
        'preguntas' => [
            '¿Se realizó una Evaluación de Impacto en la Protección de Datos antes de implementar el control de acceso biométrico?',
            '¿La comunidad institucional recibe capacitaciones periódicas sobre normativa, seguridad y protección de datos personales?',
            '¿Los operarios y desarrolladores del sistema biométrico fueron capacitados en el tratamiento de datos personales sensibles y en los procedimientos institucionales aplicables?'
        ],
        'pesos' => [33.33, 33.33, 33.34],
    ],
];

function renderCategoria(int $id, array $categoria): void
{
    foreach ($categoria['preguntas'] as $index => $texto) {
        $preguntaId = $index + 1;
        $peso = $categoria['pesos'][$index];
        ?>
        <article class="pregunta-item">
            <div class="pregunta-numero"><?php echo $preguntaId; ?></div>
            <div class="pregunta-cuerpo">
                <div class="pregunta-texto"><?php echo htmlspecialchars($texto, ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="pregunta-controles">
                    <div>
                        <label>Ponderación (%)</label>
                        <input type="number"
                               class="input-porcentaje"
                               name="cat<?php echo $id; ?>_peso_<?php echo $preguntaId; ?>"
                               value="<?php echo number_format($peso, 2, '.', ''); ?>"
                               min="0" max="100" step="0.01">
                    </div>
                    <div>
                        <label>Estado de cumplimiento</label>
                        <select class="select-estado"
                                name="cat<?php echo $id; ?>_estado_<?php echo $preguntaId; ?>"
                                required>
                            <option value="" selected>Seleccione una opción</option>
                            <option value="Cumple totalmente">Cumple totalmente</option>
                            <option value="Cumple parcialmente">Cumple parcialmente</option>
                            <option value="No cumple">No cumple</option>
                            <option value="No aplica">No aplica</option>
                        </select>
                    </div>
                    <div>
                        <label>Evidencia / observación</label>
                        <input type="text"
                               class="input-observacion"
                               name="cat<?php echo $id; ?>_observacion_<?php echo $preguntaId; ?>"
                               placeholder="Describa brevemente la evidencia revisada">
                    </div>
                </div>
            </div>
        </article>
        <?php
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulador LOPDP - Sistema Biométrico</title>
    <link rel="stylesheet" href="css/estilos.css?v=20260826-4">
</head>
<body>
<div class="app-shell">
    <header class="hero-header">
        <div class="hero-copy">
            <span class="project-tag">PROYECTO DE TITULACIÓN · DESARROLLO DE SOFTWARE</span>
            <h1>Simulador de Cumplimiento LOPDP</h1>
            <p>Revisión del sistema biométrico de control de acceso frente a criterios de protección de datos personales.</p>
        </div>
        <div class="institution-card">
            <span>Institución académica</span>
            <strong>Instituto Tecnológico Universitario ISMAC</strong>
        </div>
    </header>

    <?php if (!empty($_SESSION['error_formulario'])): ?>
        <div class="mensaje-error">
            <?php
            echo htmlspecialchars($_SESSION['error_formulario'], ENT_QUOTES, 'UTF-8');
            unset($_SESSION['error_formulario']);
            ?>
        </div>
    <?php endif; ?>

    <section class="wizard-status">
        <div class="wizard-status-top">
            <div class="wizard-current">
                <span id="paso-actual-label" class="wizard-kicker">Paso 1 de 7</span>
                <strong id="paso-actual-titulo">Información general</strong>
            </div>
            <div class="wizard-percent"><span id="avance-pasos-num">14</span>% completado</div>
        </div>
        <div class="wizard-track">
            <div id="avance-pasos-bar" class="wizard-fill" style="width:14.28%"></div>
        </div>
    </section>

    <div class="work-layout">
        <aside class="step-sidebar" aria-label="Pasos del simulador">
            <div class="sidebar-title">Proceso de simulación</div>

            <button class="step-tab activo" data-tab="tab-general" data-step="1" type="button">
                <span class="step-index">1</span>
                <span><strong>Información general</strong><small>Datos de la simulación</small></span>
            </button>

            <button class="step-tab bloqueado" data-tab="tab-cat1" data-step="2" type="button">
                <span class="step-index">2</span>
                <span><strong>Categoría 1</strong><small>Políticas institucionales</small></span>
            </button>

            <button class="step-tab bloqueado" data-tab="tab-cat2" data-step="3" type="button">
                <span class="step-index">3</span>
                <span><strong>Categoría 2</strong><small>Sistema biométrico</small></span>
            </button>

            <button class="step-tab bloqueado" data-tab="tab-cat3" data-step="4" type="button">
                <span class="step-index">4</span>
                <span><strong>Categoría 3</strong><small>Actores del sistema</small></span>
            </button>

            <button class="step-tab bloqueado" data-tab="tab-hallazgos" data-step="5" type="button">
                <span class="step-index">5</span>
                <span><strong>Hallazgos</strong><small>Revisión automática</small></span>
            </button>

            <button class="step-tab bloqueado" data-tab="tab-cierre" data-step="6" type="button">
                <span class="step-index">6</span>
                <span><strong>Cierre</strong><small>Conclusiones y recomendaciones</small></span>
            </button>

            <button class="step-tab bloqueado" data-tab="tab-dashboard" data-step="7" type="button">
                <span class="step-index">7</span>
                <span><strong>Dashboard</strong><small>Resultados finales</small></span>
            </button>

            <button class="step-help" data-tab="tab-acerca" type="button">Acerca del simulador</button>
        </aside>

        <main class="main-stage">
            <form id="form-evaluacion" action="guardar_evaluacion.php" method="POST" novalidate>
                <input type="hidden"
                       name="csrf_token"
                       value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

                <section id="tab-general" class="tab-contenido activo" data-step="1">
                    <div class="card section-card accent-blue">
                        <div class="section-heading">
                            <div class="section-title-row">
                                <span class="section-number">01</span>
                                <div>
                                    <h2>Información general</h2>
                                    <p>Registre los datos que identificarán esta simulación.</p>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="nombre_institucion">Nombre de la institución *</label>
                                <input type="text"
                                       id="nombre_institucion"
                                       name="nombre_institucion"
                                       value="Instituto Tecnológico Universitario ISMAC"
                                       required>
                            </div>
                            <div class="form-group">
                                <label for="ruc">RUC / Identificación <span class="campo-opcional">(opcional)</span></label>
                                <input type="text" id="ruc" name="ruc" placeholder="No obligatorio">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="nombre_sistema">Sistema analizado *</label>
                                <input type="text"
                                       id="nombre_sistema"
                                       name="nombre_sistema"
                                       value="Sistema Biométrico de Control de Acceso"
                                       required>
                            </div>
                            <div class="form-group">
                                <label for="fecha_evaluacion">Fecha de simulación *</label>
                                <input type="date"
                                       id="fecha_evaluacion"
                                       name="fecha_evaluacion"
                                       value="<?php echo date('Y-m-d'); ?>"
                                       required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="evaluador">Responsable de la simulación *</label>
                            <input type="text"
                                   id="evaluador"
                                   name="evaluador"
                                   value="David Fernando Bustillos Rosas"
                                   required>
                        </div>

                        <div class="btn-grupo btn-end">
                            <button type="button"
                                    class="btn btn-primary"
                                    onclick="avanzarPaso('tab-general','tab-cat1')">
                                Continuar a Categoría 1
                            </button>
                        </div>
                    </div>
                </section>

                <?php foreach ($categorias as $id => $categoria): ?>
                <section id="tab-cat<?php echo $id; ?>"
                         class="tab-contenido"
                         data-step="<?php echo $id + 1; ?>">
                    <div class="card section-card accent-cat<?php echo $id; ?>">
                        <div class="section-heading">
                            <div class="section-title-row">
                                <span class="section-number">0<?php echo $id + 1; ?></span>
                                <div>
                                    <h2>Categoría <?php echo $id; ?></h2>
                                    <p><?php echo htmlspecialchars($categoria['titulo'], ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="category-note">
                            Seleccione el estado que corresponda a cada requisito y registre la evidencia revisada.
                        </div>

                        <div id="categoria-<?php echo $id; ?>">
                            <?php renderCategoria($id, $categoria); ?>
                        </div>

                        <div class="peso-total">
                            Ponderación de la categoría:
                            <strong id="peso-total-cat<?php echo $id; ?>">100.00%</strong>
                        </div>

                        <div class="btn-grupo btn-between">
                            <button type="button"
                                    class="btn btn-secondary"
                                    onclick="mostrarTab('<?php echo $id === 1 ? 'tab-general' : 'tab-cat' . ($id - 1); ?>')">
                                Anterior
                            </button>
                            <button type="button"
                                    class="btn btn-primary"
                                    onclick="avanzarPaso('tab-cat<?php echo $id; ?>','<?php echo $id === 3 ? 'tab-hallazgos' : 'tab-cat' . ($id + 1); ?>')">
                                Guardar paso y continuar
                            </button>
                        </div>
                    </div>
                </section>
                <?php endforeach; ?>

                <section id="tab-hallazgos" class="tab-contenido" data-step="5">
                    <div class="card section-card accent-amber">
                        <div class="section-heading">
                            <div class="section-title-row">
                                <span class="section-number">05</span>
                                <div>
                                    <h2>Hallazgos identificados</h2>
                                    <p>Requisitos que presentan cumplimiento parcial o incumplimiento.</p>
                                </div>
                            </div>
                        </div>

                        <div id="hallazgos-container"></div>

                        <div class="pdf-note">
                            Los hallazgos quedan registrados en el sistema, pero no forman parte del informe PDF final.
                        </div>

                        <div class="btn-grupo btn-between">
                            <button type="button" class="btn btn-secondary" onclick="mostrarTab('tab-cat3')">Anterior</button>
                            <button type="button" class="btn btn-primary" onclick="avanzarPaso('tab-hallazgos','tab-cierre')">Continuar</button>
                        </div>
                    </div>
                </section>

                <section id="tab-cierre" class="tab-contenido" data-step="6">
                    <div class="card section-card accent-purple">
                        <div class="section-heading">
                            <div class="section-title-row">
                                <span class="section-number">06</span>
                                <div>
                                    <h2>Conclusiones y recomendaciones</h2>
                                    <p>Registre el cierre técnico de la simulación.</p>
                                </div>
                            </div>
                        </div>

                        <div class="editor-grid">
                            <div class="form-group">
                                <label for="conclusiones">Conclusiones</label>
                                <textarea id="conclusiones"
                                          name="conclusiones"
                                          rows="8"
                                          placeholder="Describa los resultados principales obtenidos..."></textarea>
                            </div>

                            <div class="form-group">
                                <label for="recomendaciones">Recomendaciones</label>
                                <textarea id="recomendaciones"
                                          name="recomendaciones"
                                          rows="8"
                                          placeholder="Detalle las acciones técnicas o administrativas recomendadas..."></textarea>
                            </div>
                        </div>

                        <div class="btn-grupo btn-between">
                            <button type="button" class="btn btn-secondary" onclick="mostrarTab('tab-hallazgos')">Anterior</button>
                            <button type="button" class="btn btn-primary" onclick="avanzarPaso('tab-cierre','tab-dashboard')">Ver dashboard</button>
                        </div>
                    </div>
                </section>

                <section id="tab-dashboard" class="tab-contenido" data-step="7">
                    <div class="card section-card dashboard-final">
                        <div class="section-heading">
                            <div class="section-title-row">
                                <span class="section-number">07</span>
                                <div>
                                    <h2>Dashboard de resultados</h2>
                                    <p>Resumen de cumplimiento antes de guardar la simulación.</p>
                                </div>
                            </div>
                        </div>

                        <div class="score-grid">
                            <div class="score-card score-blue">
                                <div class="score-ring small"
                                     id="dash-ring-cat1"
                                     style="--score:0; --ring:#2563eb">
                                    <div class="score-ring-inner">
                                        <strong id="porcentaje-cat1">0%</strong>
                                        <span>Categoría 1</span>
                                    </div>
                                </div>
                                <h3>Políticas institucionales</h3>
                            </div>

                            <div class="score-card score-green">
                                <div class="score-ring small"
                                     id="dash-ring-cat2"
                                     style="--score:0; --ring:#16a34a">
                                    <div class="score-ring-inner">
                                        <strong id="porcentaje-cat2">0%</strong>
                                        <span>Categoría 2</span>
                                    </div>
                                </div>
                                <h3>Sistema biométrico</h3>
                            </div>

                            <div class="score-card score-purple">
                                <div class="score-ring small"
                                     id="dash-ring-cat3"
                                     style="--score:0; --ring:#7c3aed">
                                    <div class="score-ring-inner">
                                        <strong id="porcentaje-cat3">0%</strong>
                                        <span>Categoría 3</span>
                                    </div>
                                </div>
                                <h3>Actores del sistema</h3>
                            </div>

                            <div class="score-card score-dark">
                                <div class="score-ring small"
                                     id="dash-ring-general"
                                     style="--score:0; --ring:#06b6d4">
                                    <div class="score-ring-inner">
                                        <strong id="promedio-general">0%</strong>
                                        <span>Promedio</span>
                                    </div>
                                </div>
                                <h3 id="nivel-general">Sin evaluar</h3>
                            </div>
                        </div>

                        <div class="chart-container">
                            <div class="chart-box">
                                <h3>Cumplimiento por categoría</h3>
                                <div class="bar-chart">
                                    <div class="bar-item bar-primary">
                                        <span class="bar-label">Políticas institucionales</span>
                                        <div class="bar-track"><div id="barra-detalle-cat1" class="bar-fill" style="width:0%"></div></div>
                                        <span id="detalle-porcentaje-cat1" class="bar-percent">0%</span>
                                    </div>
                                    <div class="bar-item bar-success">
                                        <span class="bar-label">Sistema biométrico</span>
                                        <div class="bar-track"><div id="barra-detalle-cat2" class="bar-fill" style="width:0%"></div></div>
                                        <span id="detalle-porcentaje-cat2" class="bar-percent">0%</span>
                                    </div>
                                    <div class="bar-item bar-warning">
                                        <span class="bar-label">Actores del sistema</span>
                                        <div class="bar-track"><div id="barra-detalle-cat3" class="bar-fill" style="width:0%"></div></div>
                                        <span id="detalle-porcentaje-cat3" class="bar-percent">0%</span>
                                    </div>
                                </div>
                            </div>

                            <div class="chart-box">
                                <h3>Distribución de respuestas</h3>
                                <div class="donut-wrap">
                                    <div id="donut-estados" class="donut-chart">
                                        <div class="donut-center">
                                            <strong id="donut-total">0</strong>
                                            <span>respuestas</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="metrics-grid">
                                    <div class="metric-item">
                                        <span class="metric-dot dot-green"></span>
                                        <span class="metric-text">Cumple totalmente</span>
                                        <strong id="totales-cumple">0</strong>
                                    </div>
                                    <div class="metric-item">
                                        <span class="metric-dot dot-amber"></span>
                                        <span class="metric-text">Cumple parcialmente</span>
                                        <strong id="totales-parcial">0</strong>
                                    </div>
                                    <div class="metric-item">
                                        <span class="metric-dot dot-red"></span>
                                        <span class="metric-text">No cumple</span>
                                        <strong id="totales-no-cumple">0</strong>
                                    </div>
                                    <div class="metric-item">
                                        <span class="metric-dot dot-gray"></span>
                                        <span class="metric-text">No aplica</span>
                                        <strong id="totales-no-aplica">0</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="metodologia-nota">
                            <strong>Metodología:</strong>
                            Cumple totalmente = 100% del peso; cumple parcialmente = 50%;
                            no cumple = 0%; “No aplica” se excluye del denominador.
                        </div>

                        <div class="btn-grupo btn-between">
                            <button type="button" class="btn btn-secondary" onclick="mostrarTab('tab-cierre')">Anterior</button>
                            <button type="submit" class="btn btn-success">Guardar simulación y abrir dashboard final</button>
                        </div>
                    </div>
                </section>

                <section id="tab-acerca" class="tab-contenido">
                    <div class="card section-card accent-slate">
                        <div class="section-heading">
                            <div class="section-title-row">
                                <div>
                                    <h2>Acerca del simulador</h2>
                                    <p>Información técnica y académica del proyecto.</p>
                                </div>
                            </div>
                        </div>

                        <div class="info-grid">
                            <article class="info-bloque">
                                <h3>Base normativa</h3>
                                <p>El simulador toma como referencia la Ley Orgánica de Protección de Datos Personales del Ecuador y su Reglamento General.</p>
                            </article>
                            <article class="info-bloque">
                                <h3>Funcionamiento</h3>
                                <p>La simulación revisa políticas institucionales, controles del sistema biométrico y responsabilidades de sus actores.</p>
                            </article>
                            <article class="info-bloque">
                                <h3>Tecnologías</h3>
                                <p>PHP, MySQL, JavaScript, HTML y CSS. El informe final se genera con TCPDF.</p>
                            </article>
                            <article class="info-bloque">
                                <h3>Alcance</h3>
                                <p>El resultado sirve como apoyo académico y técnico. No reemplaza una auditoría jurídica o de ciberseguridad especializada.</p>
                            </article>
                        </div>

                        <div class="btn-grupo">
                            <button type="button" class="btn btn-secondary" onclick="volverPasoActual()">Volver a la simulación</button>
                        </div>
                    </div>
                </section>
            </form>
        </main>
    </div>
</div>

<script src="js/funciones.js?v=20260826-4"></script>
</body>
</html>
