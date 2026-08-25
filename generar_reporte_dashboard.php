<?php
// =============================================
// GENERAR PDF REAL CON TCPDF (CON GRÁFICOS)
// =============================================

// Incluir TCPDF
require_once('tcpdf/tcpdf.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Obtener porcentajes
    $cat1 = intval($data['cat1'] ?? 0);
    $cat2 = intval($data['cat2'] ?? 0);
    $cat3 = intval($data['cat3'] ?? 0);
    $promedio = round(($cat1 + $cat2 + $cat3) / 3);
    
    // =============================================
    // CREAR PDF CON TCPDF
    // =============================================
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    
    // Configurar documento
    $pdf->SetCreator('Sistema de Evaluación LOPDP');
    $pdf->SetAuthor($data['evaluador'] ?? 'Evaluador');
    $pdf->SetTitle('Informe de Evaluación LOPDP');
    $pdf->SetSubject('Evaluación de Cumplimiento');
    $pdf->SetKeywords('LOPDP, Evaluación, Biométrico');
    
    // Configurar márgenes
    $pdf->SetMargins(15, 20, 15);
    $pdf->SetAutoPageBreak(true, 20);
    
    // Agregar página
    $pdf->AddPage();
    
    // =============================================
    // TÍTULO
    // =============================================
    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->Cell(0, 12, 'INFORME DE EVALUACION LOPDP', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 8, 'Sistema de Control de Acceso Biometrico', 0, 1, 'C');
    $pdf->Cell(0, 8, 'Carrera de Desarrollo de Software - ISMAC', 0, 1, 'C');
    $pdf->Ln(8);
    
    // =============================================
    // INFORMACIÓN GENERAL
    // =============================================
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, '1. INFORMACION GENERAL', 0, 1);
    $pdf->SetFont('helvetica', '', 11);
    
    $informacion = [
        'Institucion:' => $data['institucion'] ?? 'No registrado',
        'RUC:' => $data['ruc'] ?? 'No registrado',
        'Sistema:' => $data['sistema'] ?? 'No registrado',
        'Fecha:' => $data['fecha'] ?? date('Y-m-d'),
        'Evaluador:' => $data['evaluador'] ?? 'No registrado'
    ];
    
    foreach ($informacion as $label => $valor) {
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(40, 7, $label, 0, 0);
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 7, htmlspecialchars($valor), 0, 1);
    }
    $pdf->Ln(5);
    
    // =============================================
    // RESULTADOS POR CATEGORÍA
    // =============================================
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, '2. RESULTADOS POR CATEGORIA', 0, 1);
    
    $categorias = [
        1 => ['nombre' => 'Politicas Institucionales', 'porcentaje' => $cat1],
        2 => ['nombre' => 'Sistema Biometrico', 'porcentaje' => $cat2],
        3 => ['nombre' => 'Actores del Sistema', 'porcentaje' => $cat3]
    ];
    
    foreach ($categorias as $cat) {
        $porc = $cat['porcentaje'];
        
        // Nombre de la categoría
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(80, 8, $cat['nombre'] . ':', 0, 0);
        
        // Porcentaje con color
        $pdf->SetFont('helvetica', 'B', 13);
        if ($porc >= 80) {
            $pdf->SetTextColor(16, 185, 129); // Verde
        } elseif ($porc >= 50) {
            $pdf->SetTextColor(245, 158, 11); // Amarillo
        } else {
            $pdf->SetTextColor(239, 68, 68); // Rojo
        }
        $pdf->Cell(30, 8, $porc . '%', 0, 0);
        $pdf->SetTextColor(0, 0, 0);
        
        // Barra de progreso (dibujada con rectángulos)
        $pdf->SetDrawColor(200, 200, 200);
        $pdf->SetFillColor(200, 200, 200);
        $pdf->Rect(120, $pdf->GetY() - 1, 60, 6, 'F');
        
        // Barra de progreso llena
        if ($porc >= 80) {
            $pdf->SetFillColor(16, 185, 129);
        } elseif ($porc >= 50) {
            $pdf->SetFillColor(245, 158, 11);
        } else {
            $pdf->SetFillColor(239, 68, 68);
        }
        $pdf->Rect(120, $pdf->GetY() - 1, ($porc * 60 / 100), 6, 'F');
        
        $pdf->Ln(10);
    }
    $pdf->Ln(5);
    
    // =============================================
    // PROMEDIO GENERAL
    // =============================================
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, '3. PROMEDIO GENERAL', 0, 1);
    $pdf->SetFont('helvetica', 'B', 16);
    if ($promedio >= 80) {
        $pdf->SetTextColor(16, 185, 129);
        $estado = '✅ Cumplimiento excelente';
    } elseif ($promedio >= 50) {
        $pdf->SetTextColor(245, 158, 11);
        $estado = '⚠️ Cumplimiento parcial';
    } else {
        $pdf->SetTextColor(239, 68, 68);
        $estado = '❌ Cumplimiento bajo';
    }
    $pdf->Cell(0, 10, $promedio . '% - ' . $estado, 0, 1);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(5);
    
    // =============================================
    // CONCLUSIONES
    // =============================================
    if (!empty($data['conclusiones']) && $data['conclusiones'] !== 'No hay conclusiones') {
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, '4. CONCLUSIONES', 0, 1);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->MultiCell(0, 7, htmlspecialchars($data['conclusiones']), 0, 'L');
        $pdf->Ln(3);
    }
    
    // =============================================
    // RECOMENDACIONES
    // =============================================
    if (!empty($data['recomendaciones']) && $data['recomendaciones'] !== 'No hay recomendaciones') {
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, '5. RECOMENDACIONES', 0, 1);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->MultiCell(0, 7, htmlspecialchars($data['recomendaciones']), 0, 'L');
    }
    
    // =============================================
    // PIE DE PÁGINA
    // =============================================
    $pdf->SetY(-20);
    $pdf->SetFont('helvetica', 'I', 9);
    $pdf->Cell(0, 8, 'Documento generado por el Sistema de Evaluacion LOPDP', 0, 0, 'C');
    $pdf->Cell(0, 8, date('d/m/Y H:i'), 0, 0, 'R');
    
    // =============================================
    // DESCARGAR PDF
    // =============================================
    $pdf->Output('informe_evaluacion_lopdp.pdf', 'D');
    exit;
}
?>