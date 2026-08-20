-- =============================================
-- BASE DE DATOS PARA EVALUACIÓN LOPDP
-- SISTEMA DE EVALUACIÓN DE CUMPLIMIENTO NORMATIVO
-- =============================================

CREATE DATABASE IF NOT EXISTS evaluacion_lopdp;
USE evaluacion_lopdp;

-- Tabla de evaluaciones
CREATE TABLE IF NOT EXISTS evaluaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_institucion VARCHAR(255) NOT NULL,
    ruc VARCHAR(20) NULL,
    nombre_sistema VARCHAR(255) NOT NULL,
    fecha_evaluacion DATE NOT NULL,
    evaluador VARCHAR(255) NOT NULL,
    estado VARCHAR(20) DEFAULT 'activa',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de respuestas
CREATE TABLE IF NOT EXISTS respuestas_evaluacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evaluacion_id INT NOT NULL,
    categoria INT NOT NULL,
    pregunta_id INT NOT NULL,
    pregunta_texto TEXT NOT NULL,
    porcentaje DECIMAL(5,2) DEFAULT 0,
    estado_cumplimiento VARCHAR(20) DEFAULT 'pendiente',
    observacion TEXT,
    evidencia TEXT,
    FOREIGN KEY (evaluacion_id) REFERENCES evaluaciones(id) ON DELETE CASCADE
);

-- Tabla de hallazgos
CREATE TABLE IF NOT EXISTS hallazgos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evaluacion_id INT NOT NULL,
    categoria INT NOT NULL,
    pregunta_id INT NOT NULL,
    descripcion TEXT NOT NULL,
    FOREIGN KEY (evaluacion_id) REFERENCES evaluaciones(id) ON DELETE CASCADE
);

-- Tabla de conclusiones y recomendaciones
CREATE TABLE IF NOT EXISTS conclusiones_recomendaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evaluacion_id INT NOT NULL,
    conclusiones TEXT,
    recomendaciones TEXT,
    FOREIGN KEY (evaluacion_id) REFERENCES evaluaciones(id) ON DELETE CASCADE
);