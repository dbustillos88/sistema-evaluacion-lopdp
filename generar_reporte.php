<?php
// =============================================
// GENERAR REPORTE PDF - VERSIÓN CORREGIDA
// USANDO TCPDF O DOM PDF
// =============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // =============================================
    // OPCIÓN 1: USAR TCPDF (RECOMENDADO)
    // =============================================
    
    // Si tienes TCPDF instalado (composer require tecnickcom/tcpdf)
    // require_once('vendor/tecnickcom/tcpdf/tcpdf.php');
    
    // =============================================
    // OPCIÓN 2: USAR HTML + CSS + window.print()
    // (FUNCIONA SIN INSTALAR NADA)
    // =============================================
    
    header('Content-Type: text/html; charset=UTF-8');
    header('Content-Disposition: attachment; filename="informe_evaluacion_lopdp.html"');
    
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
            h2 { 
                color: #1E293B;
                border-bottom: 3px solid #4F46E5;
                padding-bottom: 10px;
                margin: 30px 0 20px 0;
                font-size: 20px;
            }
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
            .card-resultado {
                background: #F8FAFC;
                padding: 20px;
                border-radius: 10px;
                margin: 15px 0;
                border-left: 4px solid #4F46E5;
                display: inline-block;
                width: 30%;
                text-align: center;
            }
            .card-resultado .numero {
                font-size: 36px;
                font-weight: 800;
            }
            .verde { color: #10B981; }
            .amarillo { color: #F59E0B; }
            .rojo { color: #EF4444; }
            .hallazgo {
                background: #FFFBEB;
                padding: 15px;
                border-radius: 8px;
                margin: 8px 0;
                border-left: 4px solid #F59E0B;
            }
            .footer {
                margin-top: 40px;
                text-align: center;
                color: #94A3B8;
                font-size: 12px;
                border-top: 1px solid #E2E8F0;
                padding-top: 20px;
            }
            .resultados-grid {
                display: flex;
                gap: 20px;
                justify-content: center;
                flex-wrap: wrap;
                margin: 20px 0;
            }
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
            @media print {
                body { margin: 20px; }
                .no-print { display: none; }
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>📊 INFORME DE EVALUACIÓN LOPDP</h1>
            <p>Sistema de Control de Acceso Biométrico</p>
            <p>Carrera de Desarrollo de Software - ISMAC</p>
        </div>
        
        <h2>1. INFORMACIÓN GENERAL</h2>
        <table>
            <tr><td><strong>Institución</strong></td><td>' . htmlspecialchars($data['institucion'] ?? 'No registrado') . '</td></tr>
            <tr><td><strong>RUC</strong></td><td>' . htmlspecialchars($data['ruc'] ?? 'No registrado') . '</td></tr>
            <tr><td><strong>Sistema Evaluado</strong></td><td>' . htmlspecialchars($data['sistema'] ?? 'No registrado') . '</td></tr>
            <tr><td><strong>Fecha</strong></td><td>' . htmlspecialchars($data['fecha'] ?? date('Y-m-d')) . '</td></tr>
            <tr><td><strong>Evaluador</strong></td><td>' . htmlspecialchars($data['evaluador'] ?? 'No registrado') . '</td></tr>
        </table>
        
        <h2>2. RESULTADOS POR CATEGORÍA</h2>
        <div class="resultados-grid">';
    
    $categorias = [
        1 => ['nombre' => 'Políticas Institucionales', 'porcentaje' => $data['cat1'] ?? '0%'],
        2 => ['nombre' => 'Sistema Biométrico', 'porcentaje' => $data['cat2'] ?? '0%'],
        3 => ['nombre' => 'Actores del Sistema', 'porcentaje' => $data['cat3'] ?? '0%']
    ];
    
    foreach ($categorias as $cat) {
        $porc = intval($cat['porcentaje']);
        $color = $porc >= 80 ? 'verde' : ($porc >= 50 ? 'amarillo' : 'rojo');
        $html .= '
        <div class="card-resultado">
            <strong>' . $cat['nombre'] . '</strong>
            <div class="numero ' . $color . '">' . $cat['porcentaje'] . '</div>
        </div>';
    }
    
    $html .= '
        </div>';
    
    if (!empty($data['hallazgos']) && is_array($data['hallazgos'])) {
        $html .= '<h2>3. HALLAZGOS IDENTIFICADOS</h2>';
        foreach ($data['hallazgos'] as $hallazgo) {
            if (!empty($hallazgo)) {
                $html .= '<div class="hallazgo">• ' . htmlspecialchars($hallazgo) . '</div>';
            }
        }
    }
    
    if (!empty($data['conclusiones'])) {
        $html .= '
        <h2>4. CONCLUSIONES</h2>
        <div class="conclusiones">
            <p>' . nl2br(htmlspecialchars($data['conclusiones'])) . '</p>
        </div>';
    }
    
    if (!empty($data['recomendaciones'])) {
        $html .= '
        <h2>5. RECOMENDACIONES</h2>
        <div class="recomendaciones">
            <p>' . nl2br(htmlspecialchars($data['recomendaciones'])) . '</p>
        </div>';
    }
    
    $html .= '
        <div class="footer">
            <p>Documento generado por el Sistema de Evaluación LOPDP</p>
            <p>Fecha: ' . date('d/m/Y H:i') . '</p>
        </div>
    </body>
    </html>';
    
    echo $html;
    exit;
}
?>