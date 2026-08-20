<?php
// =============================================
// CONFIGURACIÓN DE CONEXIÓN A BASE DE DATOS
// SISTEMA DE EVALUACIÓN LOPDP
// =============================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'evaluacion_lopdp');

/**
 * Obtiene la conexión a la base de datos
 * @return mysqli Objeto de conexión
 */
function obtenerConexion() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            throw new Exception("Error de conexión: " . $conn->connect_error);
        }
        return $conn;
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}

/**
 * Guarda una evaluación completa en la base de datos
 * @param array $data Datos de la evaluación
 * @return int ID de la evaluación guardada
 */
function guardarEvaluacion($data) {
    $conn = obtenerConexion();
    
    $stmt = $conn->prepare("INSERT INTO evaluaciones (nombre_institucion, ruc, nombre_sistema, fecha_evaluacion, evaluador) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", 
        $data['nombre_institucion'], 
        $data['ruc'], 
        $data['nombre_sistema'], 
        $data['fecha_evaluacion'], 
        $data['evaluador']
    );
    $stmt->execute();
    $evaluacion_id = $conn->insert_id;
    
    foreach ($data['respuestas'] as $respuesta) {
        $stmt = $conn->prepare("INSERT INTO respuestas_evaluacion (evaluacion_id, categoria, pregunta_id, pregunta_texto, porcentaje, estado_cumplimiento, observacion, evidencia) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiisssss",
            $evaluacion_id,
            $respuesta['categoria'],
            $respuesta['pregunta_id'],
            $respuesta['pregunta_texto'],
            $respuesta['porcentaje'],
            $respuesta['estado'],
            $respuesta['observacion'],
            $respuesta['evidencia']
        );
        $stmt->execute();
    }
    
    foreach ($data['hallazgos'] as $hallazgo) {
        $stmt = $conn->prepare("INSERT INTO hallazgos (evaluacion_id, categoria, pregunta_id, descripcion) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $evaluacion_id, $hallazgo['categoria'], $hallazgo['pregunta_id'], $hallazgo['descripcion']);
        $stmt->execute();
    }
    
    if (isset($data['conclusiones']) || isset($data['recomendaciones'])) {
        $stmt = $conn->prepare("INSERT INTO conclusiones_recomendaciones (evaluacion_id, conclusiones, recomendaciones) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $evaluacion_id, $data['conclusiones'], $data['recomendaciones']);
        $stmt->execute();
    }
    
    return $evaluacion_id;
}

/**
 * Obtiene los datos de una evaluación
 * @param int $evaluacion_id ID de la evaluación
 * @return array Datos de la evaluación
 */
function obtenerEvaluacion($evaluacion_id) {
    $conn = obtenerConexion();
    $stmt = $conn->prepare("SELECT * FROM evaluaciones WHERE id = ?");
    $stmt->bind_param("i", $evaluacion_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Obtiene las respuestas de una evaluación
 * @param int $evaluacion_id ID de la evaluación
 * @return array Lista de respuestas
 */
function obtenerRespuestas($evaluacion_id) {
    $conn = obtenerConexion();
    $stmt = $conn->prepare("SELECT * FROM respuestas_evaluacion WHERE evaluacion_id = ? ORDER BY categoria, pregunta_id");
    $stmt->bind_param("i", $evaluacion_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $respuestas = [];
    while ($row = $result->fetch_assoc()) {
        $respuestas[] = $row;
    }
    return $respuestas;
}

/**
 * Obtiene los hallazgos de una evaluación
 * @param int $evaluacion_id ID de la evaluación
 * @return array Lista de hallazgos
 */
function obtenerHallazgos($evaluacion_id) {
    $conn = obtenerConexion();
    $stmt = $conn->prepare("SELECT * FROM hallazgos WHERE evaluacion_id = ?");
    $stmt->bind_param("i", $evaluacion_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $hallazgos = [];
    while ($row = $result->fetch_assoc()) {
        $hallazgos[] = $row;
    }
    return $hallazgos;
}

/**
 * Obtiene conclusiones y recomendaciones de una evaluación
 * @param int $evaluacion_id ID de la evaluación
 * @return array Conclusiones y recomendaciones
 */
function obtenerConclusiones($evaluacion_id) {
    $conn = obtenerConexion();
    $stmt = $conn->prepare("SELECT * FROM conclusiones_recomendaciones WHERE evaluacion_id = ?");
    $stmt->bind_param("i", $evaluacion_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Calcula los porcentajes de cumplimiento por categoría
 * @param int $evaluacion_id ID de la evaluación
 * @return array Porcentajes por categoría
 */
function calcularPorcentajes($evaluacion_id) {
    $respuestas = obtenerRespuestas($evaluacion_id);
    $resultados = [
        1 => ['total' => 0, 'cumple' => 0, 'peso_total' => 0],
        2 => ['total' => 0, 'cumple' => 0, 'peso_total' => 0],
        3 => ['total' => 0, 'cumple' => 0, 'peso_total' => 0]
    ];
    
    foreach ($respuestas as $r) {
        $cat = $r['categoria'];
        $resultados[$cat]['total']++;
        $resultados[$cat]['peso_total'] += $r['porcentaje'];
        
        if ($r['estado_cumplimiento'] == 'Cumple totalmente') {
            $resultados[$cat]['cumple'] += $r['porcentaje'];
        } elseif ($r['estado_cumplimiento'] == 'Cumple parcialmente') {
            $resultados[$cat]['cumple'] += $r['porcentaje'] * 0.5;
        }
    }
    
    $porcentajes = [];
    foreach ($resultados as $cat => $data) {
        if ($data['peso_total'] > 0) {
            $porcentajes[$cat] = round(($data['cumple'] / $data['peso_total']) * 100, 2);
        } else {
            $porcentajes[$cat] = 0;
        }
    }
    
    return $porcentajes;
}
?>