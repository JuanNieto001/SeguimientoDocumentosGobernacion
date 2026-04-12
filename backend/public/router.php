<?php
/**
 * Archivo: backend/public/router.php
 * Proposito: Codigo documentado para mantenimiento.
 * @documentado-copilot 2026-04-11
 */

/**
 * Router script for PHP's built-in web server.
 *
 * Ensures requests with dots in the URI (e.g. CO1.PCCNTR.9088356)
 * are forwarded to index.php instead of being treated as static files.
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// If the request is for a real file that exists, serve it directly
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Otherwise, route everything through Laravel's front controller
require_once __DIR__ . '/index.php';

