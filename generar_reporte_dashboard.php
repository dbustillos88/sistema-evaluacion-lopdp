<?php
// =============================================
// GENERAR PDF CON GRÁFICOS Y DISEÑO MODERNO
// =============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Configurar headers para descargar como PDF
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="informe_evaluacion_lopdp_' . ($data['evaluacion_id'] ?? '') . '.pdf"');
    
    // Obtener porcentajes
    $cat1 = intval($data['cat1'] ?? 0);
    $cat2 = intval($data['cat2'] ?? 0);
    $cat3 = intval($data['cat3'] ?? 0);
    
    // Funciones para colores e iconos
    function getColor($porcentaje) {
        if ($porcentaje >= 80) return '#10B981';
        if ($porcentaje >= 50) return '#F59E0B';
        return '#EF4444';
    }
    
    function getIcono($porcentaje) {
        if ($porcentaje >= 80) return '✅';
        if ($porcentaje >= 50) return '⚠️';
        return '❌';
    }
    
    $color1 = getColor($cat1);
    $color2 = getColor($cat2);
    $color3 = getColor($cat3);
    
    $icono1 = getIcono($cat1);
    $icono2 = getIcono($cat2);
    $icono3 = getIcono($cat3);
    
    $promedio = round(($cat1 + $cat2 + $cat3) / 3);
    $colorPromedio = getColor($promedio);
    
    // =============================================
    // HTML DEL PDF CON GRÁFICOS
    // =============================================
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Informe de Evaluación LOPDP</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { 
                font-family: "Segoe UI", Arial, sans-serif; 
                margin: 40px; 
                background: white;
                color: #1E293B;
            }
            
            /* HEADER */
            .header {
                text-align: center;
                padding: 30px;
                background: linear-gradient(135deg, #1E1B4B, #4F46E5);
                color: white;
                border-radius: 12px;
                margin-bottom: 30px;
            }
            .header h1 { font-size: 28px; margin-bottom: 8px; }
            .header p { opacity: 0.85; font-size: 16px; }
            .header .badge {
                display: inline-block;
                background: rgba(255,255,255,0.15);
                padding: 4px 16px;
                border-radius: 20px;
                font-size: 12px;
                margin-top: 10px;
            }
            
            h2 { 
                color: #1E293B;
                border-bottom: 3px solid #4F46E5;
                padding-bottom: 10px;
                margin: 30px 0 20px 0;
                font-size: 20px;
            }
            
            /* TABLA */
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 15px 0 25px 0;
            }
            th, td {
                padding: 12px 16px;
                border: 1px solid #E2E8F0;
                text-align: left;
            }
            th {
                background: #F1F5F9;
                font-weight: 600;
                color: #475569;
            }
            
            /* TARJETAS DE RESULTADOS */
            .grid-resultados {
                display: flex;
                gap: 20px;
                justify-content: center;
                flex-wrap: wrap;
                margin: 20px 0;
            }
            .card-resultado {
                background: #F8FAFC;
                padding: 20px 25px;
                border-radius: 12px;
                border-left: 5px solid #4F46E5;
                flex: 1;
                min-width: 180px;
                text-align: center;
                box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            }
            .card-resultado .label {
                font-size: 14px;
                font-weight: 600;
                color: #64748B;
            }
            .card-resultado .numero {
                font-size: 42px;
                font-weight: 800;
                margin: 8px 0 4px 0;
            }
            .card-resultado .sub {
                font-size: 12px;
                color: #94A3B8;
            }
            .card-resultado .icono {
                font-size: 28px;
                display: block;
                margin-bottom: 4px;
            }
            
            .verde { color: #10B981; }
            .amarillo { color: #F59E0B; }
            .rojo { color: #EF4444; }
            
            /* GRÁFICO DE BARRAS */
            .bar-chart {
                margin: 20px 0;
                background: #F8FAFC;
                padding: 20px 25px;
                border-radius: 12px;
                border: 1px solid #E2E8F0;
            }
            .bar-chart .chart-title {
                font-weight: 600;
                margin-bottom: 15px;
                color: #475569;
                font-size: 15px;
            }
            .bar-item {
                display: flex;
                align-items: center;
                gap: 15px;
                margin-bottom: 12px;
            }
            .bar-item .bar-label {
                font-size: 14px;
                font-weight: 500;
                color: #475569;
                min-width: 180px;
            }
            .bar-item .bar-track {
                flex: 1;
                height: 28px;
                background: #E2E8F0;
                border-radius: 20px;
                overflow: hidden;
                position: relative;
            }
            .bar-item .bar-track .bar-fill {
                height: 100%;
                border-radius: 20px;
                display: flex;
                align-items: center;
                justify-content: flex-end;
                padding-right: 12px;
                font-size: 13px;
                font-weight: 700;
                color: white;
            }
            .bar-item .bar-percent {
                font-size: 16px;
                font-weight: 700;
                min-width: 55px;
                text-align: right;
            }
            
            /* PROMEDIO */
            .card-promedio {
                background: linear-gradient(135deg, #4F46E5, #818CF8);
                color: white;
                padding: 25px 30px;
                border-radius: 12px;
                text-align: center;
                margin: 20px 0;
            }
            .card-promedio .label {
                font-size: 14px;
                opacity: 0.8;
            }
            .card-promedio .numero {
                font-size: 48px;
                font-weight: 900;
            }
            .card-promedio .sub {
                font-size: 13px;
                opacity: 0.7;
            }
            
            /* CONCLUSIONES Y RECOMENDACIONES */
            .conclusiones {
                background: #F0FDF4;
                padding: 20px;
                border-radius: 10px;
                border-left: 4px solid #10B981;
                margin: 15px 0;
            }
            .recomendaciones {
                background: #EFF6FF;
                padding: 20px;
                border-radius: 10px;
                border-left: 4px solid #3B82F6;
                margin: 15px 0;
            }
            
            .footer {
                margin-top: 40px;
                text-align: center;
                color: #94A3B8;
                font-size: 12px;
                border-top: 1px solid #E2E8F0;
                padding-top: 20px;
            }
            
            @media print {
                body { margin: 20px; }
            }
        </style>
    </head>
    <body>
        <!-- HEADER -->
        <div class="header">
            <h1>📊 INFORME DE EVALUACIÓN LOPDP</h1>
            <p>Sistema de Control de Acceso Biométrico</p>
            <p>Carrera de Desarrollo de Software - ISMAC</p>
            <div class="badge">📅 ' . date('d/m/Y H:i') . '</div>
        </div>
        
        <!-- 1. INFORMACIÓN GENERAL -->
        <h2>1. INFORMACIÓN GENERAL</h2>
        <table>
            <tr><td><strong>Institución</strong></td><td>' . htmlspecialchars($data['institucion'] ?? 'No registrado') . '</td></tr>
            <tr><td><strong>RUC</strong></td><td>' . htmlspecialchars($data['ruc'] ?? 'No registrado') . '</td></tr>
            <tr><td><strong>Sistema Evaluado</strong></td><td>' . htmlspecialchars($data['sistema'] ?? 'No registrado') . '</td></tr>
            <tr><td><strong>Fecha</strong></td><td>' . htmlspecialchars($data['fecha'] ?? date('Y-m-d')) . '</td></tr>
            <tr><td><strong>Evaluador</strong></td><td>' . htmlspecialchars($data['evaluador'] ?? 'No registrado') . '</td></tr>
        </table>
        
        <!-- 2. RESULTADOS CON GRÁFICOS -->
        <h2>2. RESULTADOS POR CATEGORÍA</h2>
        
        <!-- TARJETAS -->
        <div class="grid-resultados">
            <div class="card-resultado" style="border-left-color: ' . $color1 . ';">
                <span class="icono">' . $icono1 . '</span>
                <div class="label">🏛️ Políticas Institucionales</div>
                <div class="numero ' . ($cat1 >= 80 ? 'verde' : ($cat1 >= 50 ? 'amarillo' : 'rojo')) . '">' . $cat1 . '%</div>
                <div class="sub">Cumplimiento normativo</div>
            </div>
            <div class="card-resultado" style="border-left-color: ' . $color2 . ';">
                <span class="icono">' . $icono2 . '</span>
                <div class="label">🔐 Sistema Biométrico</div>
                <div class="numero ' . ($cat2 >= 80 ? 'verde' : ($cat2 >= 50 ? 'amarillo' : 'rojo')) . '">' . $cat2 . '%</div>
                <div class="sub">Ciberseguridad y gestión</div>
            </div>
            <div class="card-resultado" style="border-left-color: ' . $color3 . ';">
                <span class="icono">' . $icono3 . '</span>
                <div class="label">👥 Actores del Sistema</div>
                <div class="numero ' . ($cat3 >= 80 ? 'verde' : ($cat3 >= 50 ? 'amarillo' : 'rojo')) . '">' . $cat3 . '%</div>
                <div class="sub">Capacitación y conocimiento</div>
            </div>
        </div>
        
        <!-- GRÁFICO DE BARRAS -->
        <div class="bar-chart">
            <div class="chart-title">📊 Nivel de Cumplimiento por Categoría</div>
            
            <div class="bar-item">
                <span class="bar-label">🏛️ Políticas Institucionales</span>
                <div class="bar-track">
                    <div class="bar-fill" style="width:' . $cat1 . '%; background: ' . $color1 . ';">' . $cat1 . '%</div>
                </div>
                <span class="bar-percent" style="color:' . $color1 . ';">' . $cat1 . '%</span>
            </div>
            
            <div class="bar-item">
                <span class="bar-label">🔐 Sistema Biométrico</span>
                <div class="bar-track">
                    <div class="bar-fill" style="width:' . $cat2 . '%; background: ' . $color2 . ';">' . $cat2 . '%</div>
                </div>
                <span class="bar-percent" style="color:' . $color2 . ';">' . $cat2 . '%</span>
            </div>
            
            <div class="bar-item">
                <span class="bar-label">👥 Actores del Sistema</span>
                <div class="bar-track">
                    <div class="bar-fill" style="width:' . $cat3 . '%; background: ' . $color3 . ';">' . $cat3 . '%</div>
                </div>
                <span class="bar-percent" style="color:' . $color3 . ';">' . $cat3 . '%</span>
            </div>
        </div>
        
        <!-- PROMEDIO GENERAL -->
        <div class="card-promedio">
            <div class="label">📈 PROMEDIO GENERAL</div>
            <div class="numero">' . $promedio . '%</div>
            <div class="sub">' . ($promedio >= 80 ? '✅ Cumplimiento excelente' : ($promedio >= 50 ? '⚠️ Cumplimiento parcial' : '❌ Cumplimiento bajo')) . '</div>
        </div>
        
        <!-- 4. CONCLUSIONES -->';
    
    if (!empty($data['conclusiones']) && $data['conclusiones'] !== 'No hay conclusiones') {
        $html .= '
        <h2>4. CONCLUSIONES</h2>
        <div class="conclusiones">
            <p>' . nl2br(htmlspecialchars($data['conclusiones'])) . '</p>
        </div>';
    }
    
    // 5. RECOMENDACIONES
    if (!empty($data['recomendaciones']) && $data['recomendaciones'] !== 'No hay recomendaciones') {
        $html .= '
        <h2>5. RECOMENDACIONES</h2>
        <div class="recomendaciones">
            <p>' . nl2br(htmlspecialchars($data['recomendaciones'])) . '</p>
        </div>';
    }
    
    $html .= '
        <!-- PIE DE PÁGINA -->
        <div class="footer">
            <p>Documento generado por el Sistema de Evaluación LOPDP</p>
            <p>Instituto Tecnológico Universitario ISMAC</p>
            <p>Generado: ' . date('d/m/Y H:i') . '</p>
        </div>
    </body>
    </html>';
    
    echo $html;
    exit;
}
?>