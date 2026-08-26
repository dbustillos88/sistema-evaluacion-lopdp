<?php
// TCPDF 6.8+ referencia constantes de cURL al cargar la clase.
// Estos valores permiten generar PDFs locales incluso si la extensión cURL no está habilitada.
$fallbackCurlConstants = [
    'CURLOPT_URL' => 10002,
    'CURLOPT_RETURNTRANSFER' => 19913,
    'CURLOPT_FOLLOWLOCATION' => 52,
    'CURLOPT_FAILONERROR' => 45,
    'CURLOPT_CONNECTTIMEOUT' => 78,
    'CURLOPT_MAXREDIRS' => 68,
    'CURLOPT_PROTOCOLS' => 181,
    'CURLOPT_SSL_VERIFYHOST' => 81,
    'CURLOPT_SSL_VERIFYPEER' => 64,
    'CURLOPT_TIMEOUT' => 13,
    'CURLOPT_USERAGENT' => 10018,
    'CURLPROTO_HTTP' => 1,
    'CURLPROTO_HTTPS' => 2,
    'CURLPROTO_FTP' => 4,
    'CURLPROTO_FTPS' => 8,
];
foreach ($fallbackCurlConstants as $name => $value) {
    if (!defined($name)) {
        define($name, $value);
    }
}
unset($fallbackCurlConstants, $name, $value);

require_once __DIR__ . '/../tcpdf/tcpdf.php';
