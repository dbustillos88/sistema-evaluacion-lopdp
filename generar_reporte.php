<?php
// =============================================
// GENERAR PDF CON TCPDF - VERSIÓN SIMPLIFICADA
// =============================================

// Incluir TCPDF (ruta correcta)
require_once('tcpdf/tcpdf.php');

// Si aún da error, prueba con ruta absoluta:
// require_once('C:/xampp/htdocs/sistema_evaluacion_lopdp/tcpdf/tcpdf.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // =============================================
    // CREAR PDF CON TCPDF
    // =============================================
    
    // Crear nuevo documento PDF
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    
    // Configurar documento
    $pdf->SetCreator('Sistema de Evaluación LOPDP');
    $pdf->SetAuthor($data['evaluador'] ?? 'Evaluador');
    $pdf->SetTitle('Informe de Evaluación LOPDP');
    $pdf->SetSubject('Evaluación de Cumplimiento');
    
    // Configurar márgenes
    $pdf->SetMargins(20, 25, 20);
    $pdf->SetAutoPageBreak(true, 25);
    
    // Agregar página
    $pdf->AddPage();
    
    // =============================================
    // TÍTULO
    // =============================================
    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->Cell(0, 12, 'INFORME DE EVALUACIÓN LOPDP', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 8, 'Sistema de Control de Acceso Biométrico', 0, 1, 'C');
    $pdf->Cell(0, 8, 'Carrera de Desarrollo de Software - ISMAC', 0, 1, 'C');
    $pdf->Ln(8);
    
    // =============================================
    // INFORMACIÓN GENERAL
    // =============================================
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, '1. INFORMACIÓN GENERAL', 0, 1);
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell(45, 7, 'Institución:', 0, 0);
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 7, htmlspecialchars($data['institucion'] ?? 'No registrado'), 0, 1);
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell(45, 7, 'RUC:', 0, 0);
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 7, htmlspecialchars($data['ruc'] ?? 'No registrado'), 0, 1);
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell(45, 7, 'Sistema:', 0, 0);
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 7, htmlspecialchars($data['sistema'] ?? 'No registrado'), 0, 1);
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell(45, 7, 'Fecha:', 0, 0);
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 7, htmlspecialchars($data['fecha'] ?? date('Y-m-d')), 0, 1);
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell(45, 7, 'Evaluador:', 0, 0);
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 7, htmlspecialchars($data['evaluador'] ?? 'No registrado'), 0, 1);
    $pdf->Ln(5);
    
    // =============================================
    // RESULTADOS POR CATEGORÍA
    // =============================================
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, '2. RESULTADOS POR CATEGORÍA', 0, 1);
    
    $categorias = [
        1 => ['nombre' => 'Políticas Institucionales', 'porcentaje' => $data['cat1'] ?? '0%'],
        2 => ['nombre' => 'Sistema Biométrico', 'porcentaje' => $data['cat2'] ?? '0%'],
        3 => ['nombre' => 'Actores del Sistema', 'porcentaje' => $data['cat3'] ?? '0%']
    ];
    
    foreach ($categorias as $cat) {
        $porc = intval($cat['porcentaje']);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(100, 8, $cat['nombre'] . ':', 0, 0);
        $pdf->SetFont('helvetica', 'B', 13);
        
        if ($porc >= 80) {
            $pdf->SetTextColor(16, 185, 129);
        } elseif ($porc >= 50) {
            $pdf->SetTextColor(245, 158, 11);
        } else {
            $pdf->SetTextColor(239, 68, 68);
        }
        
        $pdf->Cell(0, 8, $cat['porcentaje'], 0, 1);
        $pdf->SetTextColor(0, 0, 0);
    }
    $pdf->Ln(5);
    
    // =============================================
    // HALLAZGOS
    // =============================================
    if (!empty($data['hallazgos']) && is_array($data['hallazgos'])) {
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, '3. HALLAZGOS IDENTIFICADOS', 0, 1);
        $pdf->SetFont('helvetica', '', 10);
        foreach ($data['hallazgos'] as $hallazgo) {
            if (!empty($hallazgo)) {
                $pdf->MultiCell(0, 6, '• ' . htmlspecialchars($hallazgo), 0, 'L');
            }
        }
        $pdf->Ln(3);
    }
    
    // =============================================
    // CONCLUSIONES
    // =============================================
    if (!empty($data['conclusiones'])) {
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, '4. CONCLUSIONES', 0, 1);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->MultiCell(0, 7, htmlspecialchars($data['conclusiones']), 0, 'L');
        $pdf->Ln(3);
    }
    
    // =============================================
    // RECOMENDACIONES
    // =============================================
    if (!empty($data['recomendaciones'])) {
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
    $pdf->Cell(0, 8, 'Documento generado por el Sistema de Evaluación LOPDP', 0, 0, 'C');
    $pdf->Cell(0, 8, date('d/m/Y H:i'), 0, 0, 'R');
    
    // =============================================
    // DESCARGAR PDF
    // =============================================
    $pdf->Output('informe_evaluacion_lopdp.pdf', 'D');
    exit;
}
?>