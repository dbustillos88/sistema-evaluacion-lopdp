<?php
require_once __DIR__ . '/../config/conexion.php';

function assertIgual($esperado, $actual, string $mensaje): void
{
    if ($esperado !== $actual) {
        fwrite(STDERR, "FALLO: {$mensaje}. Esperado=" . var_export($esperado, true) . ' Actual=' . var_export($actual, true) . PHP_EOL);
        exit(1);
    }
}

$respuestas = [
    ['categoria' => 1, 'porcentaje' => 50, 'estado_cumplimiento' => 'Cumple totalmente'],
    ['categoria' => 1, 'porcentaje' => 50, 'estado_cumplimiento' => 'No aplica'],
    ['categoria' => 2, 'porcentaje' => 100, 'estado_cumplimiento' => 'Cumple parcialmente'],
    ['categoria' => 3, 'porcentaje' => 100, 'estado_cumplimiento' => 'No cumple'],
];

$m = calcularMetricasDesdeRespuestas($respuestas);
assertIgual(100.0, (float) $m['porcentajes'][1], 'No aplica debe excluirse del denominador');
assertIgual(50.0, (float) $m['porcentajes'][2], 'Cumple parcialmente debe aportar 50%');
assertIgual(0.0, (float) $m['porcentajes'][3], 'No cumple debe aportar 0%');
assertIgual(50.0, (float) $m['promedio_general'], 'Promedio general');
assertIgual('Medio', $m['nivel'], 'Clasificación de nivel');
assertIgual(1, $m['estados']['No aplica'], 'Conteo de No aplica');

echo "OK - motor de métricas\n";
