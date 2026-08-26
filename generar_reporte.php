<?php
require_once 'config/conexion.php';
require_once 'config/tcpdf_compat.php';

class InformeLopdpPdf extends TCPDF
{
    public function Footer(): void
    {
        $this->SetY(-14);
        $this->SetFont('helvetica', '', 8);
        $this->SetTextColor(100, 116, 139);
        $this->Cell(0, 6, 'Simulador de Cumplimiento LOPDP', 0, 0, 'L');
        $this->Cell(0, 6, 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(), 0, 0, 'R');
    }
}

function limpiarPdf(string $texto): string
{
    $texto = trim(strip_tags($texto));
    return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $texto) ?? '';
}

function asegurarEspacio(TCPDF $pdf, float $alto): void
{
    if ($pdf->GetY() + $alto > 274) {
        $pdf->AddPage();
    }
}

function colorPorcentaje(float $valor): array
{
    if ($valor >= 80) return [16, 185, 129];
    if ($valor >= 50) return [245, 158, 11];
    return [239, 68, 68];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: text/plain; charset=UTF-8');
    exit('Método no permitido.');
}

try {
    $payload = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
    $evaluacionId = filter_var($payload['evaluacion_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$evaluacionId || $evaluacionId < 1) {
        throw new InvalidArgumentException('ID de simulación inválido.');
    }

    $evaluacion = obtenerEvaluacion((int) $evaluacionId);
    if (!$evaluacion) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=UTF-8');
        exit('Simulación no encontrada.');
    }

    $respuestas = obtenerRespuestas((int) $evaluacionId);
    $hallazgos = obtenerHallazgos((int) $evaluacionId);
    $conclusiones = obtenerConclusiones((int) $evaluacionId) ?? ['conclusiones' => '', 'recomendaciones' => ''];
    $metricas = calcularMetricasDesdeRespuestas($respuestas);

    $pdf = new InformeLopdpPdf('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('Simulador LOPDP');
    $pdf->SetAuthor(limpiarPdf($evaluacion['evaluador']));
    $pdf->SetTitle('Informe del Simulador LOPDP #' . $evaluacionId);
    $pdf->SetSubject('Resultado del simulador de cumplimiento del sistema biométrico');
    $pdf->SetMargins(16, 18, 16);
    $pdf->SetAutoPageBreak(true, 20);
    $pdf->setPrintHeader(false);
    $pdf->AddPage();

    // Portada compacta
    $pdf->SetFillColor(15, 23, 42);
    $pdf->Rect(0, 0, 210, 48, 'F');
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('helvetica', 'B', 19);
    $pdf->SetXY(16, 14);
    $pdf->Cell(178, 9, 'INFORME DEL SIMULADOR LOPDP', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10.5);
    $pdf->SetX(16);
    $pdf->Cell(178, 7, limpiarPdf($evaluacion['nombre_sistema']), 0, 1, 'L');
    $pdf->SetX(16);
    $pdf->SetTextColor(203, 213, 225);
    $pdf->Cell(178, 6, 'Simulación #' . $evaluacionId . ' · Generado el ' . date('d/m/Y H:i'), 0, 1, 'L');
    $pdf->SetTextColor(15, 23, 42);
    $pdf->SetY(56);

    // 1. Información general
    $pdf->SetFont('helvetica', 'B', 13);
    $pdf->Cell(0, 8, '1. Información general', 0, 1);
    $pdf->SetFont('helvetica', '', 10);
    $info = [
        'Institución' => $evaluacion['nombre_institucion'],
        'RUC / Identificación' => $evaluacion['ruc'] ?: 'No registrado',
        'Sistema analizado' => $evaluacion['nombre_sistema'],
        'Fecha de simulación' => date('d/m/Y', strtotime($evaluacion['fecha_evaluacion'])),
        'Responsable' => $evaluacion['evaluador'],
    ];
    foreach ($info as $label => $valor) {
        $pdf->SetFont('helvetica', 'B', 9.5);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(42, 7, $label . ':', 0, 0);
        $pdf->SetFont('helvetica', '', 9.5);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->MultiCell(136, 7, limpiarPdf((string) $valor), 0, 'L', false, 1);
    }
    $pdf->Ln(3);

    // 2. Resumen ejecutivo
    $pdf->SetFont('helvetica', 'B', 13);
    $pdf->Cell(0, 8, '2. Resumen de cumplimiento', 0, 1);

    $promedio = (float) $metricas['promedio_general'];
    [$r, $g, $b] = colorPorcentaje($promedio);
    $pdf->SetFillColor(248, 250, 252);
    $pdf->SetDrawColor(226, 232, 240);
    $x = 16;
    $y = $pdf->GetY();
    $pdf->RoundedRect($x, $y, 178, 24, 3, '1111', 'DF');
    $pdf->SetXY($x + 6, $y + 5);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetTextColor(100, 116, 139);
    $pdf->Cell(60, 5, 'PROMEDIO GENERAL', 0, 0);
    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->SetTextColor($r, $g, $b);
    $pdf->SetXY($x + 6, $y + 10);
    $pdf->Cell(40, 8, number_format($promedio, 2, ',', '.') . '%', 0, 0);
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetTextColor(15, 23, 42);
    $pdf->SetXY($x + 75, $y + 9);
    $pdf->Cell(95, 7, 'Nivel: ' . $metricas['nivel'], 0, 1);
    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor(100, 116, 139);
    $pdf->SetXY($x + 75, $y + 15);
    $pdf->Cell(95, 5, $metricas['total_preguntas'] . ' preguntas evaluadas', 0, 1);
    $pdf->SetY($y + 29);

    // Barras por categoría
    foreach ($metricas['categorias'] as $categoria) {
        $valor = (float) $categoria['porcentaje'];
        [$cr, $cg, $cb] = colorPorcentaje($valor);
        $pdf->SetFont('helvetica', 'B', 9.5);
        $pdf->SetTextColor(51, 65, 85);
        $pdf->Cell(62, 6, limpiarPdf($categoria['nombre']), 0, 0);
        $pdf->SetFont('helvetica', 'B', 9.5);
        $pdf->SetTextColor($cr, $cg, $cb);
        $pdf->Cell(20, 6, number_format($valor, 2, ',', '.') . '%', 0, 0, 'R');
        $barX = $pdf->GetX() + 4;
        $barY = $pdf->GetY() + 1.5;
        $barW = 90;
        $pdf->SetFillColor(226, 232, 240);
        $pdf->RoundedRect($barX, $barY, $barW, 3.5, 1.5, '1111', 'F');
        $pdf->SetFillColor($cr, $cg, $cb);
        $fill = max(0, min($barW, $barW * ($valor / 100)));
        if ($fill > 0) $pdf->RoundedRect($barX, $barY, $fill, 3.5, 1.5, '1111', 'F');
        $pdf->Ln(8);
    }
    $pdf->Ln(2);

    // Distribución de estados
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetTextColor(15, 23, 42);
    $pdf->Cell(0, 7, 'Distribución de estados', 0, 1);
    $estadoRows = [
        ['Cumple totalmente', $metricas['estados']['Cumple totalmente'], [16,185,129]],
        ['Cumple parcialmente', $metricas['estados']['Cumple parcialmente'], [245,158,11]],
        ['No cumple', $metricas['estados']['No cumple'], [239,68,68]],
        ['No aplica', $metricas['estados']['No aplica'], [148,163,184]],
    ];
    foreach ($estadoRows as [$label, $count, $color]) {
        $pdf->SetFillColor(...$color);
        $pdf->Circle($pdf->GetX() + 2, $pdf->GetY() + 3.2, 1.5, 0, 360, 'F');
        $pdf->SetX($pdf->GetX() + 7);
        $pdf->SetFont('helvetica', '', 9.5);
        $pdf->Cell(70, 6, $label, 0, 0);
        $pdf->SetFont('helvetica', 'B', 9.5);
        $pdf->Cell(15, 6, (string) $count, 0, 1, 'R');
    }

    $pdf->Ln(4);
    $pdf->SetFillColor(238, 242, 255);
    $pdf->SetTextColor(55, 48, 163);
    $pdf->SetFont('helvetica', '', 8.8);
    $pdf->MultiCell(
        0,
        6,
        'Metodología: Cumple totalmente = 100% del peso; Cumple parcialmente = 50%; No cumple = 0%; No aplica se excluye del denominador. El promedio general corresponde al promedio de las categorías aplicables.',
        0,
        'L',
        true,
        1
    );
    $pdf->SetTextColor(15, 23, 42);

 // 3. Conclusiones y recomendaciones
$seccion = 3;

    // Conclusiones y recomendaciones
    $seccion = 4;
    foreach ([
        'Conclusiones' => (string) ($conclusiones['conclusiones'] ?? ''),
        'Recomendaciones' => (string) ($conclusiones['recomendaciones'] ?? ''),
    ] as $titulo => $texto) {
        $texto = limpiarPdf($texto);
        if ($texto === '') continue;
        asegurarEspacio($pdf, 22);
        $pdf->Ln(4);
        $pdf->SetFont('helvetica', 'B', 13);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(0, 8, $seccion . '. ' . $titulo, 0, 1);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(51, 65, 85);
        $pdf->MultiCell(0, 6, $texto, 0, 'L', false, 1);
        $seccion++;
    }

    header('Content-Type: application/pdf');
    $pdf->Output('informe_simulador_lopdp_' . $evaluacionId . '.pdf', 'D');
    exit;
} catch (JsonException|InvalidArgumentException $e) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=UTF-8');
    exit($e->getMessage());
} catch (Throwable $e) {
    error_log('Error generando reporte LOPDP: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    exit('No fue posible generar el informe PDF.');
}
