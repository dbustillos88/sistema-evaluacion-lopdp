<?php
// =============================================
// GUARDAR EVALUACIÓN
// SISTEMA DE EVALUACIÓN DE CUMPLIMIENTO LOPDP
// PROYECTO DE TITULACIÓN - ISMAC
// =============================================
require_once 'config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nombre_institucion' => $_POST['nombre_institucion'] ?? '',
        'ruc' => $_POST['ruc'] ?? '',
        'nombre_sistema' => $_POST['nombre_sistema'] ?? '',
        'fecha_evaluacion' => $_POST['fecha_evaluacion'] ?? date('Y-m-d'),
        'evaluador' => $_POST['evaluador'] ?? '',
        'respuestas' => [],
        'hallazgos' => [],
        'conclusiones' => $_POST['conclusiones'] ?? '',
        'recomendaciones' => $_POST['recomendaciones'] ?? ''
    ];

    // Categoría 1 (7 preguntas)
    for ($i = 1; $i <= 7; $i++) {
        $data['respuestas'][] = [
            'categoria' => 1,
            'pregunta_id' => $i,
            'pregunta_texto' => $_POST["cat1_texto_$i"] ?? "Pregunta $i",
            'porcentaje' => $_POST["cat1_peso_$i"] ?? 0,
            'estado' => $_POST["cat1_estado_$i"] ?? 'pendiente',
            'observacion' => $_POST["cat1_observacion_$i"] ?? '',
            'evidencia' => $_POST["cat1_observacion_$i"] ?? ''
        ];
        
        $estado = $_POST["cat1_estado_$i"] ?? '';
        if ($estado == 'No cumple' || $estado == 'Cumple parcialmente') {
            $data['hallazgos'][] = [
                'categoria' => 1,
                'pregunta_id' => $i,
                'descripcion' => "Categoría 1 - Pregunta $i: " . ($_POST["cat1_texto_$i"] ?? '') . " - Estado: $estado"
            ];
        }
    }

    // Categoría 2 (15 preguntas)
    for ($i = 1; $i <= 15; $i++) {
        $data['respuestas'][] = [
            'categoria' => 2,
            'pregunta_id' => $i,
            'pregunta_texto' => $_POST["cat2_texto_$i"] ?? "Pregunta $i",
            'porcentaje' => $_POST["cat2_peso_$i"] ?? 0,
            'estado' => $_POST["cat2_estado_$i"] ?? 'pendiente',
            'observacion' => $_POST["cat2_observacion_$i"] ?? '',
            'evidencia' => $_POST["cat2_observacion_$i"] ?? ''
        ];
        
        $estado = $_POST["cat2_estado_$i"] ?? '';
        if ($estado == 'No cumple' || $estado == 'Cumple parcialmente') {
            $data['hallazgos'][] = [
                'categoria' => 2,
                'pregunta_id' => $i,
                'descripcion' => "Categoría 2 - Pregunta $i: " . ($_POST["cat2_texto_$i"] ?? '') . " - Estado: $estado"
            ];
        }
    }

    // Categoría 3 (8 preguntas)
    for ($i = 1; $i <= 8; $i++) {
        $data['respuestas'][] = [
            'categoria' => 3,
            'pregunta_id' => $i,
            'pregunta_texto' => $_POST["cat3_texto_$i"] ?? "Pregunta $i",
            'porcentaje' => $_POST["cat3_peso_$i"] ?? 0,
            'estado' => $_POST["cat3_estado_$i"] ?? 'pendiente',
            'observacion' => $_POST["cat3_observacion_$i"] ?? '',
            'evidencia' => $_POST["cat3_observacion_$i"] ?? ''
        ];
        
        $estado = $_POST["cat3_estado_$i"] ?? '';
        if ($estado == 'No cumple' || $estado == 'Cumple parcialmente') {
            $data['hallazgos'][] = [
                'categoria' => 3,
                'pregunta_id' => $i,
                'descripcion' => "Categoría 3 - Pregunta $i: " . ($_POST["cat3_texto_$i"] ?? '') . " - Estado: $estado"
            ];
        }
    }

    $evaluacion_id = guardarEvaluacion($data);
    
    if ($evaluacion_id) {
        header("Location: dashboard.php?id=$evaluacion_id&success=1");
    } else {
        header("Location: index.php?error=1");
    }
    exit;
}
?>