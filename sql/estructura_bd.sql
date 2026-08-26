-- Base de datos del Simulador de Cumplimiento LOPDP
CREATE DATABASE IF NOT EXISTS evaluacion_lopdp
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE evaluacion_lopdp;

CREATE TABLE IF NOT EXISTS evaluaciones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_institucion VARCHAR(255) NOT NULL,
    ruc VARCHAR(20) NULL,
    nombre_sistema VARCHAR(255) NOT NULL,
    fecha_evaluacion DATE NOT NULL,
    evaluador VARCHAR(255) NOT NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'activa',
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_evaluaciones_fecha (fecha_evaluacion),
    INDEX idx_evaluaciones_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS respuestas_evaluacion (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    evaluacion_id INT UNSIGNED NOT NULL,
    categoria TINYINT UNSIGNED NOT NULL,
    pregunta_id SMALLINT UNSIGNED NOT NULL,
    pregunta_texto TEXT NOT NULL,
    porcentaje DECIMAL(6,2) NOT NULL DEFAULT 0,
    estado_cumplimiento VARCHAR(30) NOT NULL DEFAULT 'No cumple',
    observacion TEXT NULL,
    evidencia TEXT NULL,
    CONSTRAINT fk_respuesta_evaluacion
      FOREIGN KEY (evaluacion_id) REFERENCES evaluaciones(id) ON DELETE CASCADE,
    UNIQUE KEY uq_respuesta_evaluacion_pregunta (evaluacion_id, categoria, pregunta_id),
    INDEX idx_respuestas_categoria (evaluacion_id, categoria),
    INDEX idx_respuestas_estado (estado_cumplimiento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS hallazgos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    evaluacion_id INT UNSIGNED NOT NULL,
    categoria TINYINT UNSIGNED NOT NULL,
    pregunta_id SMALLINT UNSIGNED NOT NULL,
    descripcion TEXT NOT NULL,
    CONSTRAINT fk_hallazgo_evaluacion
      FOREIGN KEY (evaluacion_id) REFERENCES evaluaciones(id) ON DELETE CASCADE,
    INDEX idx_hallazgos_evaluacion (evaluacion_id, categoria, pregunta_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS conclusiones_recomendaciones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    evaluacion_id INT UNSIGNED NOT NULL,
    conclusiones TEXT NULL,
    recomendaciones TEXT NULL,
    CONSTRAINT fk_conclusiones_evaluacion
      FOREIGN KEY (evaluacion_id) REFERENCES evaluaciones(id) ON DELETE CASCADE,
    INDEX idx_conclusiones_evaluacion (evaluacion_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
