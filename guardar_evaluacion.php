<?php
require_once 'config/conexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$csrf = (string) ($_POST['csrf_token'] ?? '');
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
    http_response_code(403);
    exit('Solicitud no válida. Recargue el formulario e inténtelo nuevamente.');
}

function postTexto(string $key, int $max = 4000): string
{
    $value = trim((string) ($_POST[$key] ?? ''));
    return function_exists('mb_substr') ? mb_substr($value, 0, $max) : substr($value, 0, $max);
}

function postPeso(string $key): float
{
    $value = filter_var($_POST[$key] ?? 0, FILTER_VALIDATE_FLOAT);
    if ($value === false) {
        return 0.0;
    }
    return max(0.0, min(100.0, (float) $value));
}

try {
    $nombreInstitucion = postTexto('nombre_institucion', 255);
    $ruc = postTexto('ruc', 20);
    $nombreSistema = postTexto('nombre_sistema', 255);
    $fechaEvaluacion = postTexto('fecha_evaluacion', 10);
    $evaluador = postTexto('evaluador', 255);

    if ($nombreInstitucion === '' || $nombreSistema === '' || $evaluador === '') {
        throw new InvalidArgumentException('Institución, sistema y evaluador son obligatorios.');
    }

    $fecha = DateTime::createFromFormat('Y-m-d', $fechaEvaluacion);
    if (!$fecha || $fecha->format('Y-m-d') !== $fechaEvaluacion) {
        throw new InvalidArgumentException('La fecha de simulación no es válida.');
    }

    $cantidadPreguntas = [1 => 7, 2 => 15, 3 => 3];
    $respuestas = [];
    $hallazgos = [];

    foreach ($cantidadPreguntas as $categoria => $cantidad) {
        $sumaPesosCategoria = 0.0;
        for ($i = 1; $i <= $cantidad; $i++) {
            $estado = postTexto("cat{$categoria}_estado_{$i}", 30);
            if (!in_array($estado, ESTADOS_CUMPLIMIENTO, true)) {
                throw new InvalidArgumentException("Estado inválido en categoría {$categoria}, pregunta {$i}.");
            }

            $preguntaTexto = postTexto("cat{$categoria}_texto_{$i}", 3000);
            $peso = postPeso("cat{$categoria}_peso_{$i}");
            $sumaPesosCategoria += $peso;
            $observacion = postTexto("cat{$categoria}_observacion_{$i}", 4000);

            $respuestas[] = [
                'categoria' => $categoria,
                'pregunta_id' => $i,
                'pregunta_texto' => $preguntaTexto !== '' ? $preguntaTexto : "Pregunta {$i}",
                'porcentaje' => $peso,
                'estado' => $estado,
                'observacion' => $observacion,
                'evidencia' => $observacion,
            ];

            if ($estado === 'No cumple' || $estado === 'Cumple parcialmente') {
                $detalle = "{$estado}. {$preguntaTexto}";
                if ($observacion !== '') {
                    $detalle .= " Evidencia/observación: {$observacion}";
                }
                $hallazgos[] = [
                    'categoria' => $categoria,
                    'pregunta_id' => $i,
                    'descripcion' => $detalle,
                ];
            }
        }

        if (abs($sumaPesosCategoria - 100.0) > 0.05) {
            throw new InvalidArgumentException("La ponderación de la categoría {$categoria} debe sumar 100%. Valor recibido: " . number_format($sumaPesosCategoria, 2) . '%');
        }
    }

    $data = [
        'nombre_institucion' => $nombreInstitucion,
        'ruc' => $ruc,
        'nombre_sistema' => $nombreSistema,
        'fecha_evaluacion' => $fechaEvaluacion,
        'evaluador' => $evaluador,
        'respuestas' => $respuestas,
        'hallazgos' => $hallazgos,
        'conclusiones' => postTexto('conclusiones', 12000),
        'recomendaciones' => postTexto('recomendaciones', 12000),
    ];

    $evaluacionId = guardarEvaluacion($data);
    header("Location: dashboard.php?id={$evaluacionId}&success=1");
    exit;
} catch (Throwable $e) {
    $_SESSION['error_formulario'] = $e instanceof InvalidArgumentException
        ? $e->getMessage()
        : 'No fue posible guardar la simulación. Revise la conexión y la base de datos.';
    header('Location: index.php');
    exit;
}
