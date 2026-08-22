<?php
// =============================================
// GENERAR PDF - VERSIÓN SIN DOMPDF
// (Funciona sin instalar nada)
// =============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Configurar headers para descargar como PDF (aunque sea HTML)
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="informe_evaluacion_lopdp.pdf"');
    
    // Este es HTML, pero el navegador lo convierte a PDF al imprimir
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Informe de Evaluación LOPDP</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: Arial, sans-serif; margin: 40px; background: white; }
            h1 { color: #1a237e; text-align: center; }
            h2 { color: #1a237e; border-bottom: 2px solid #e8eaf6; padding-bottom: 10px; margin-top: 30px; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
            th { background: #1a237e; color: white; }
            .resultado { font-size: 24px; font-weight: bold; }
            .verde { color: #2e7d32; }
            .amarillo { color: #f57c00; }
            .rojo { color: #c62828; }
            .card { background: #f5f5f5; padding: 15px; margin: 15px 0; border-radius: 8px; border-left: 4px solid #1a237e; }
            .hallazgo { background: #fff3e0; padding: 10px; margin: 5px 0; border-left: 4px solid #f57c00; }
            .footer { margin-top: 40px; text-align: center; color: #999; font-size: 12px; border-top: 1px solid #ddd; padding-top: 20px; }
        </style>
    </head>
    <body>
        <h1>📊 INFORME DE EVALUACIÓN LOPDP</h1>
        <p style="text-align:center;">Sistema de Control de Acceso Biométrico - ISMAC</p>
        
        <h2>1. INFORMACIÓN GENERAL</h2>
        <table>
            <tr><td><strong>Institución</strong></td><td>' . htmlspecialchars($data['institucion'] ?? 'No registrado') . '</td></tr>
            <tr><td><strong>RUC</strong></td><td>' . htmlspecialchars($data['ruc'] ?? 'No registrado') . '</td></tr>
            <tr><td><strong>Sistema</strong></td><td>' . htmlspecialchars($data['sistema'] ?? 'No registrado') . '</td></tr>
            <tr><td><strong>Fecha</strong></td><td>' . htmlspecialchars($data['fecha'] ?? date('Y-m-d')) . '</td></tr>
            <tr><td><strong>Evaluador</strong></td><td>' . htmlspecialchars($data['evaluador'] ?? 'No registrado') . '</td></tr>
        </table>
        
        <h2>2. RESULTADOS</h2>';
    
    $categorias = [
        1 => ['nombre' => 'Políticas Institucionales', 'porcentaje' => $data['cat1'] ?? '0%'],
        2 => ['nombre' => 'Sistema Biométrico', 'porcentaje' => $data['cat2'] ?? '0%'],
        3 => ['nombre' => 'Actores del Sistema', 'porcentaje' => $data['cat3'] ?? '0%']
    ];
    
    foreach ($categorias as $cat) {
        $porc = intval($cat['porcentaje']);
        $color = $porc >= 80 ? 'verde' : ($porc >= 50 ? 'amarillo' : 'rojo');
        $html .= '
        <div class="card">
            <h3>' . $cat['nombre'] . '</h3>
            <div class="resultado ' . $color . '">' . $cat['porcentaje'] . '</div>
        </div>';
    }
    
    if (!empty($data['hallazgos']) && is_array($data['hallazgos'])) {
        $html .= '<h2>3. HALLAZGOS</h2>';
        foreach ($data['hallazgos'] as $hallazgo) {
            if (!empty($hallazgo)) {
                $html .= '<div class="hallazgo">• ' . htmlspecialchars($hallazgo) . '</div>';
            }
        }
    }
    
    if (!empty($data['conclusiones'])) {
        $html .= '<h2>4. CONCLUSIONES</h2>';
        $html .= '<p>' . nl2br(htmlspecialchars($data['conclusiones'])) . '</p>';
    }
    
    if (!empty($data['recomendaciones'])) {
        $html .= '<h2>5. RECOMENDACIONES</h2>';
        $html .= '<p>' . nl2br(htmlspecialchars($data['recomendaciones'])) . '</p>';
    }
    
    $html .= '
        <div class="footer">
            <p>Generado: ' . date('d/m/Y H:i') . '</p>
        </div>
    </body>
    </html>';
    
    echo $html;
    exit;
}
?>