# Simulador de Cumplimiento LOPDP

Aplicación web desarrollada como proyecto académico para simular y documentar el nivel de cumplimiento de un sistema de control de acceso biométrico frente a criterios relacionados con la protección de datos personales.

La estructura de preguntas toma como referencia la **Ley Orgánica de Protección de Datos Personales del Ecuador (LOPDP)** y su Reglamento General. El resultado es una herramienta de apoyo académico y no sustituye una auditoría jurídica, técnica o de seguridad.

## Tecnologías

- PHP 7.4 o superior
- MySQL 5.7 / MySQL 8.x o MariaDB equivalente
- HTML5, CSS3 y JavaScript
- TCPDF para generación de informes PDF
- XAMPP como entorno local recomendado

El dashboard no depende de librerías gráficas externas: los gráficos se construyen con HTML, CSS y JavaScript para que la presentación funcione aunque no exista conexión a Internet.

## Instalación local con XAMPP

1. Copie la carpeta del proyecto dentro de `C:\xampp\htdocs\sistema_evaluacion_lopdp`.
2. Inicie Apache y MySQL desde XAMPP.
3. Importe `sql/estructura_bd.sql` desde phpMyAdmin.
4. Revise `config/conexion.php`. La configuración por defecto utiliza:
   - Host: `localhost`
   - Usuario: `root`
   - Contraseña: vacía
   - Base de datos: `evaluacion_lopdp`
5. Abra `http://localhost/sistema_evaluacion_lopdp/`.

También se pueden utilizar las variables de entorno `LOPDP_DB_HOST`, `LOPDP_DB_USER`, `LOPDP_DB_PASS` y `LOPDP_DB_NAME`.

## Flujo del sistema

1. Se registran los datos generales del sistema analizado.
2. Se responden los requisitos de las tres categorías.
3. El sistema genera los hallazgos de cumplimiento parcial e incumplimiento.
4. Se registran conclusiones y recomendaciones.
5. El dashboard muestra los resultados antes de guardar.
6. Al guardar, los datos quedan registrados en MySQL y se abre el dashboard final.
7. Desde el dashboard final se genera el informe PDF con los datos almacenados.

## Metodología de cálculo

Cada pregunta tiene una ponderación. La suma de los pesos de cada categoría debe ser 100 %.

- **Cumple totalmente:** aporta el 100 % del peso.
- **Cumple parcialmente:** aporta el 50 % del peso.
- **No cumple:** aporta 0 %.
- **No aplica:** se excluye del denominador.

El porcentaje de cada categoría se obtiene mediante:

`puntaje logrado / peso aplicable × 100`

El promedio general es el promedio aritmético de las categorías que tienen peso aplicable.

## Dashboard

El dashboard presenta:

- Porcentaje de cumplimiento por categoría.
- Promedio general y nivel Alto / Medio / Bajo.
- Barras comparativas por categoría.
- Distribución de estados mediante gráfico circular.
- Conteo de respuestas por estado.
- Hallazgos identificados.
- Conclusiones y recomendaciones.

## Archivos principales

```text
config/conexion.php        Conexión MySQL, persistencia y motor de métricas
config/tcpdf_compat.php    Compatibilidad de TCPDF
css/estilos.css            Diseño responsive y gráficos CSS
js/funciones.js            Navegación, cálculos y actualización del dashboard
sql/estructura_bd.sql      Estructura de la base de datos
index.php                  Simulador y dashboard previo al guardado
guardar_evaluacion.php     Validación y guardado transaccional
dashboard.php              Dashboard final con datos almacenados
generar_reporte.php        Único generador del informe PDF
tcpdf/                     Librería para creación del PDF
tests/test_metricas.php    Prueba básica del motor de cálculo
```

## Controles técnicos incluidos

- Consultas preparadas para datos variables.
- Transacción al guardar la simulación completa.
- Validación de estados, fecha, campos requeridos y ponderaciones.
- Escape HTML al mostrar información almacenada.
- Token CSRF para el guardado del formulario.
- Conexión MySQL con `utf8mb4`.
- Un único motor de cálculo para dashboard y PDF.
- El PDF se genera a partir del ID almacenado y vuelve a consultar la base de datos.

## Alcance académico

La aplicación permite demostrar el proceso de levantamiento de información, cálculo, persistencia, visualización y generación de reportes. La selección de requisitos, ponderaciones y umbrales de clasificación debe quedar justificada en la metodología de la tesis y validada con el tutor o expertos cuando corresponda.
