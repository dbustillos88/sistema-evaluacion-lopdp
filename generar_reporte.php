<?php
require_once 'config/conexion.php';
require_once 'config/tcpdf_compat.php';

class InformeLopdpPdf extends TCPDF
{
    public function Footer(): void
    {
        $this->SetY(-14);
        $this->SetFont('helvetica', '', 8);
        $this->SetTextColor(100,116,139);
        $this->Cell(0,6,'Simulador de Cumplimiento LOPDP',0,0,'L');
        $this->Cell(0,6,'Página '.$this->getAliasNumPage().' de '.$this->getAliasNbPages(),0,0,'R');
    }
}

function limpiarPdf(string $texto): string
{
    $texto = trim(strip_tags($texto));
    return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u','',$texto) ?? '';
}
function asegurarEspacio(TCPDF $pdf,float $alto): void { if($pdf->GetY()+$alto>274)$pdf->AddPage(); }
function nivelColor(float $v): array { if($v>=80)return[22,163,74]; if($v>=50)return[245,158,11]; return[239,68,68]; }

if($_SERVER['REQUEST_METHOD']!=='POST'){
    http_response_code(405); header('Content-Type:text/plain; charset=UTF-8'); exit('Método no permitido.');
}

try{
    $payload=json_decode(file_get_contents('php://input'),true,512,JSON_THROW_ON_ERROR);
    $evaluacionId=filter_var($payload['evaluacion_id']??null,FILTER_VALIDATE_INT);
    if(!$evaluacionId||$evaluacionId<1)throw new InvalidArgumentException('ID de simulación inválido.');

    $evaluacion=obtenerEvaluacion((int)$evaluacionId);
    if(!$evaluacion){http_response_code(404);header('Content-Type:text/plain; charset=UTF-8');exit('Simulación no encontrada.');}
    $respuestas=obtenerRespuestas((int)$evaluacionId);
    $conclusiones=obtenerConclusiones((int)$evaluacionId)??['conclusiones'=>'','recomendaciones'=>''];
    $metricas=calcularMetricasDesdeRespuestas($respuestas);

    $pdf=new InformeLopdpPdf('P','mm','A4',true,'UTF-8',false);
    $pdf->SetCreator('Simulador LOPDP');
    $pdf->SetAuthor(limpiarPdf($evaluacion['evaluador']));
    $pdf->SetTitle('Informe del Simulador LOPDP #'.$evaluacionId);
    $pdf->SetSubject('Resultado del simulador de cumplimiento del sistema biométrico');
    $pdf->SetMargins(15,18,15); $pdf->SetAutoPageBreak(true,20); $pdf->setPrintHeader(false); $pdf->AddPage();

    // Encabezado institucional
    $pdf->SetFillColor(15,39,71); $pdf->Rect(0,0,210,46,'F');
    $pdf->SetTextColor(255,255,255); $pdf->SetFont('helvetica','B',18); $pdf->SetXY(15,12);
    $pdf->Cell(180,8,'INFORME DEL SIMULADOR LOPDP',0,1,'L');
    $pdf->SetFont('helvetica','',10); $pdf->SetX(15); $pdf->SetTextColor(205,224,244);
    $pdf->Cell(180,6,limpiarPdf($evaluacion['nombre_sistema']),0,1,'L');
    $pdf->SetX(15); $pdf->SetFont('helvetica','',8.5);
    $pdf->Cell(180,5,'Simulación #'.$evaluacionId.'  |  Generado el '.date('d/m/Y H:i'),0,1,'L');
    $pdf->SetY(53); $pdf->SetTextColor(23,32,51);

    // 1. Información general
    $pdf->SetFont('helvetica','B',12.5); $pdf->Cell(0,8,'1. Información general',0,1);
    $info=[
      'Institución'=>$evaluacion['nombre_institucion'],
      'RUC / Identificación'=>$evaluacion['ruc']?:'No registrado',
      'Sistema analizado'=>$evaluacion['nombre_sistema'],
      'Fecha de simulación'=>date('d/m/Y',strtotime($evaluacion['fecha_evaluacion'])),
      'Responsable'=>$evaluacion['evaluador'],
    ];
    foreach($info as $label=>$valor){
      $pdf->SetFillColor(248,250,252); $pdf->SetTextColor(71,85,105); $pdf->SetFont('helvetica','B',9);
      $pdf->Cell(43,7,$label.':',0,0,'L',true);
      $pdf->SetTextColor(30,41,59); $pdf->SetFont('helvetica','',9);
      $pdf->MultiCell(137,7,limpiarPdf((string)$valor),0,'L',true,1);
    }
    $pdf->Ln(4);

    // 2. Resultados
    $pdf->SetTextColor(23,32,51); $pdf->SetFont('helvetica','B',12.5); $pdf->Cell(0,8,'2. Resultados de cumplimiento',0,1);
    $prom=(float)$metricas['promedio_general']; [$nr,$ng,$nb]=nivelColor($prom);
    $pdf->SetFillColor(240,249,255); $pdf->SetDrawColor(186,230,253); $y=$pdf->GetY();
    $pdf->RoundedRect(15,$y,180,24,3,'1111','DF');
    $pdf->SetXY(21,$y+4); $pdf->SetFont('helvetica','B',8.5); $pdf->SetTextColor(71,85,105); $pdf->Cell(65,5,'PROMEDIO GENERAL',0,1);
    $pdf->SetXY(21,$y+10); $pdf->SetFont('helvetica','B',18); $pdf->SetTextColor($nr,$ng,$nb); $pdf->Cell(50,8,number_format($prom,2,',','.').'%',0,0);
    $pdf->SetXY(92,$y+8); $pdf->SetTextColor(15,39,71); $pdf->SetFont('helvetica','B',11); $pdf->Cell(95,6,'Nivel de cumplimiento: '.$metricas['nivel'],0,1);
    $pdf->SetXY(92,$y+14); $pdf->SetFont('helvetica','',8.5); $pdf->SetTextColor(100,116,139); $pdf->Cell(95,5,$metricas['total_preguntas'].' respuestas registradas',0,1);
    $pdf->SetY($y+30);

    $catColors=[1=>[37,99,235],2=>[22,163,74],3=>[124,58,237]];
    $catShort=[1=>'Políticas institucionales',2=>'Sistema biométrico',3=>'Actores del sistema'];
    $cardY=$pdf->GetY(); $cardW=56.6; $gap=5.1;
    foreach($metricas['categorias'] as $id=>$cat){
      $x=15+($id-1)*($cardW+$gap); [$cr,$cg,$cb]=$catColors[$id]; $v=(float)$cat['porcentaje'];
      $pdf->SetFillColor(248,250,252); $pdf->SetDrawColor(226,232,240); $pdf->RoundedRect($x,$cardY,$cardW,34,3,'1111','DF');
      $pdf->SetFillColor($cr,$cg,$cb); $pdf->Rect($x,$cardY,$cardW,3,'F');
      $pdf->SetXY($x+4,$cardY+7); $pdf->SetFont('helvetica','B',8); $pdf->SetTextColor(71,85,105); $pdf->MultiCell($cardW-8,5,$catShort[$id],0,'L',false,1);
      $pdf->SetXY($x+4,$cardY+17); $pdf->SetFont('helvetica','B',15); $pdf->SetTextColor($cr,$cg,$cb); $pdf->Cell($cardW-8,7,number_format($v,2,',','.').'%',0,1,'L');
      $pdf->SetXY($x+4,$cardY+27); $pdf->SetFillColor(226,232,240); $pdf->RoundedRect($x+4,$cardY+28,$cardW-8,3,1.5,'1111','F');
      $fill=max(0,min($cardW-8,($cardW-8)*($v/100))); if($fill>0){$pdf->SetFillColor($cr,$cg,$cb);$pdf->RoundedRect($x+4,$cardY+28,$fill,3,1.5,'1111','F');}
    }
    $pdf->SetY($cardY+40);

    // Distribución de estados
    $pdf->SetFont('helvetica','B',10.5); $pdf->SetTextColor(23,32,51); $pdf->Cell(0,7,'Distribución de respuestas',0,1);
    $rows=[
      ['Cumple totalmente',$metricas['estados']['Cumple totalmente'],[22,163,74]],
      ['Cumple parcialmente',$metricas['estados']['Cumple parcialmente'],[245,158,11]],
      ['No cumple',$metricas['estados']['No cumple'],[239,68,68]],
      ['No aplica',$metricas['estados']['No aplica'],[148,163,184]],
    ];
    foreach($rows as [$label,$count,$color]){
      $pdf->SetFillColor(...$color); $pdf->Circle(18,$pdf->GetY()+3,1.5,0,360,'F'); $pdf->SetX(23);
      $pdf->SetFont('helvetica','',9); $pdf->SetTextColor(71,85,105); $pdf->Cell(78,6,$label,0,0);
      $pdf->SetFont('helvetica','B',9); $pdf->SetTextColor(30,41,59); $pdf->Cell(15,6,(string)$count,0,1,'R');
    }
    $pdf->Ln(3); $pdf->SetFillColor(239,246,255); $pdf->SetTextColor(42,74,110); $pdf->SetFont('helvetica','',8.4);
    $pdf->MultiCell(0,5.5,'Metodología: Cumple totalmente = 100% del peso; Cumple parcialmente = 50%; No cumple = 0%; No aplica se excluye del denominador. El promedio general corresponde al promedio de las categorías aplicables.',0,'L',true,1);

    // Sin hallazgos en el PDF por decisión del proyecto.
    $seccion=3;
    foreach(['Conclusiones'=>(string)($conclusiones['conclusiones']??''),'Recomendaciones'=>(string)($conclusiones['recomendaciones']??'')] as $titulo=>$texto){
      $texto=limpiarPdf($texto); if($texto==='')continue; asegurarEspacio($pdf,25); $pdf->Ln(5);
      $pdf->SetFont('helvetica','B',12.5); $pdf->SetTextColor(23,32,51); $pdf->Cell(0,8,$seccion.'. '.$titulo,0,1);
      $pdf->SetFont('helvetica','',9.5); $pdf->SetTextColor(51,65,85); $pdf->MultiCell(0,6,$texto,0,'L',false,1); $seccion++;
    }

    header('Content-Type: application/pdf');
    $pdf->Output('informe_simulador_lopdp_'.$evaluacionId.'.pdf','D'); exit;
}catch(JsonException|InvalidArgumentException $e){http_response_code(400);header('Content-Type:text/plain; charset=UTF-8');exit($e->getMessage());}
catch(Throwable $e){error_log('Error generando reporte LOPDP: '.$e->getMessage());http_response_code(500);header('Content-Type:text/plain; charset=UTF-8');exit('No fue posible generar el informe PDF.');}
