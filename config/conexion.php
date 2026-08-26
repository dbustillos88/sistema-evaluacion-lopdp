<?php
// Configuración y acceso a datos del Simulador LOPDP.
if (function_exists('mysqli_report')) {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
}

define('DB_HOST', getenv('LOPDP_DB_HOST') ?: 'localhost');
define('DB_USER', getenv('LOPDP_DB_USER') ?: 'root');
define('DB_PASS', getenv('LOPDP_DB_PASS') ?: '');
define('DB_NAME', getenv('LOPDP_DB_NAME') ?: 'evaluacion_lopdp');

const ESTADOS_CUMPLIMIENTO = [
    'Cumple totalmente',
    'Cumple parcialmente',
    'No cumple',
    'No aplica',
];

function obtenerConexion(): mysqli
{
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset('utf8mb4');
    return $conn;
}

function guardarEvaluacion(array $data): int
{
    $conn = obtenerConexion();
    $conn->begin_transaction();

    try {
        $stmtEval = $conn->prepare(
            'INSERT INTO evaluaciones (nombre_institucion, ruc, nombre_sistema, fecha_evaluacion, evaluador)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmtEval->bind_param(
            'sssss',
            $data['nombre_institucion'],
            $data['ruc'],
            $data['nombre_sistema'],
            $data['fecha_evaluacion'],
            $data['evaluador']
        );
        $stmtEval->execute();
        $evaluacionId = (int) $conn->insert_id;

        $stmtRespuesta = $conn->prepare(
            'INSERT INTO respuestas_evaluacion
             (evaluacion_id, categoria, pregunta_id, pregunta_texto, porcentaje, estado_cumplimiento, observacion, evidencia)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );

        foreach ($data['respuestas'] as $respuesta) {
            $categoria = (int) $respuesta['categoria'];
            $preguntaId = (int) $respuesta['pregunta_id'];
            $preguntaTexto = (string) $respuesta['pregunta_texto'];
            $porcentaje = (float) $respuesta['porcentaje'];
            $estado = (string) $respuesta['estado'];
            $observacion = (string) $respuesta['observacion'];
            $evidencia = (string) $respuesta['evidencia'];

            $stmtRespuesta->bind_param(
                'iiidssss',
                $evaluacionId,
                $categoria,
                $preguntaId,
                $preguntaTexto,
                $porcentaje,
                $estado,
                $observacion,
                $evidencia
            );
            $stmtRespuesta->execute();
        }

        if (!empty($data['hallazgos'])) {
            $stmtHallazgo = $conn->prepare(
                'INSERT INTO hallazgos (evaluacion_id, categoria, pregunta_id, descripcion)
                 VALUES (?, ?, ?, ?)'
            );
            foreach ($data['hallazgos'] as $hallazgo) {
                $categoria = (int) $hallazgo['categoria'];
                $preguntaId = (int) $hallazgo['pregunta_id'];
                $descripcion = (string) $hallazgo['descripcion'];
                $stmtHallazgo->bind_param('iiis', $evaluacionId, $categoria, $preguntaId, $descripcion);
                $stmtHallazgo->execute();
            }
        }

        $conclusiones = (string) ($data['conclusiones'] ?? '');
        $recomendaciones = (string) ($data['recomendaciones'] ?? '');
        $stmtConclusiones = $conn->prepare(
            'INSERT INTO conclusiones_recomendaciones (evaluacion_id, conclusiones, recomendaciones)
             VALUES (?, ?, ?)'
        );
        $stmtConclusiones->bind_param('iss', $evaluacionId, $conclusiones, $recomendaciones);
        $stmtConclusiones->execute();

        $conn->commit();
        return $evaluacionId;
    } catch (Throwable $e) {
        $conn->rollback();
        throw $e;
    } finally {
        $conn->close();
    }
}

function obtenerEvaluacion(int $evaluacionId): ?array
{
    $conn = obtenerConexion();
    try {
        $stmt = $conn->prepare('SELECT * FROM evaluaciones WHERE id = ?');
        $stmt->bind_param('i', $evaluacionId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    } finally {
        $conn->close();
    }
}

function obtenerRespuestas(int $evaluacionId): array
{
    $conn = obtenerConexion();
    try {
        $stmt = $conn->prepare(
            'SELECT * FROM respuestas_evaluacion
             WHERE evaluacion_id = ? ORDER BY categoria, pregunta_id'
        );
        $stmt->bind_param('i', $evaluacionId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } finally {
        $conn->close();
    }
}

function obtenerHallazgos(int $evaluacionId): array
{
    $conn = obtenerConexion();
    try {
        $stmt = $conn->prepare(
            'SELECT * FROM hallazgos WHERE evaluacion_id = ? ORDER BY categoria, pregunta_id'
        );
        $stmt->bind_param('i', $evaluacionId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } finally {
        $conn->close();
    }
}

function obtenerConclusiones(int $evaluacionId): ?array
{
    $conn = obtenerConexion();
    try {
        $stmt = $conn->prepare(
            'SELECT * FROM conclusiones_recomendaciones
             WHERE evaluacion_id = ? ORDER BY id DESC LIMIT 1'
        );
        $stmt->bind_param('i', $evaluacionId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    } finally {
        $conn->close();
    }
}

/**
 * Calcula métricas usando una única metodología para dashboard y PDF.
 * - Cumple totalmente = 100 % del peso.
 * - Cumple parcialmente = 50 % del peso.
 * - No cumple = 0 %.
 * - No aplica se excluye del denominador.
 * - El promedio general es el promedio de las categorías con peso aplicable.
 */
function calcularMetricasDesdeRespuestas(array $respuestas): array
{
    $categorias = [
        1 => ['nombre' => 'Políticas Institucionales', 'peso_aplicable' => 0.0, 'logrado' => 0.0, 'total' => 0],
        2 => ['nombre' => 'Sistema Biométrico', 'peso_aplicable' => 0.0, 'logrado' => 0.0, 'total' => 0],
        3 => ['nombre' => 'Actores del Sistema', 'peso_aplicable' => 0.0, 'logrado' => 0.0, 'total' => 0],
    ];
    $estados = array_fill_keys(ESTADOS_CUMPLIMIENTO, 0);

    foreach ($respuestas as $r) {
        $categoria = (int) ($r['categoria'] ?? 0);
        if (!isset($categorias[$categoria])) {
            continue;
        }

        $estado = (string) ($r['estado_cumplimiento'] ?? 'No cumple');
        $peso = max(0.0, (float) ($r['porcentaje'] ?? 0));
        $categorias[$categoria]['total']++;
        if (isset($estados[$estado])) {
            $estados[$estado]++;
        }

        if ($estado === 'No aplica') {
            continue;
        }

        $categorias[$categoria]['peso_aplicable'] += $peso;
        if ($estado === 'Cumple totalmente') {
            $categorias[$categoria]['logrado'] += $peso;
        } elseif ($estado === 'Cumple parcialmente') {
            $categorias[$categoria]['logrado'] += $peso * 0.5;
        }
    }

    $porcentajes = [];
    $categoriasAplicables = [];
    foreach ($categorias as $id => &$cat) {
        $score = $cat['peso_aplicable'] > 0
            ? round(($cat['logrado'] / $cat['peso_aplicable']) * 100, 2)
            : 0.0;
        $cat['porcentaje'] = $score;
        $porcentajes[$id] = $score;
        if ($cat['peso_aplicable'] > 0) {
            $categoriasAplicables[] = $score;
        }
    }
    unset($cat);

    $promedio = $categoriasAplicables
        ? round(array_sum($categoriasAplicables) / count($categoriasAplicables), 2)
        : 0.0;

    $totalPreguntas = array_sum(array_column($categorias, 'total'));

    return [
        'categorias' => $categorias,
        'porcentajes' => $porcentajes,
        'estados' => $estados,
        'promedio_general' => $promedio,
        'total_preguntas' => $totalPreguntas,
        'nivel' => clasificarCumplimiento($promedio),
    ];
}

function calcularMetricasEvaluacion(int $evaluacionId): array
{
    return calcularMetricasDesdeRespuestas(obtenerRespuestas($evaluacionId));
}

// Compatibilidad con el código anterior.
function calcularPorcentajes(int $evaluacionId): array
{
    return calcularMetricasEvaluacion($evaluacionId)['porcentajes'];
}

function clasificarCumplimiento(float $porcentaje): string
{
    if ($porcentaje >= 80) {
        return 'Alto';
    }
    if ($porcentaje >= 50) {
        return 'Medio';
    }
    return 'Bajo';
}
