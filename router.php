<?php
/**
 * PHP built-in server router for Velmora Bank.
 * Handles clean URLs and static file serving.
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$docRoot = __DIR__;

// Serve static files directly
if ($uri !== '/' && file_exists($docRoot . $uri) && !is_dir($docRoot . $uri)) {
    return false;
}

// Try directory index
if (is_dir($docRoot . $uri)) {
    $candidates = [
        rtrim($docRoot . $uri, '/') . '/index.php',
        rtrim($docRoot . $uri, '/') . '/index.html',
    ];
    foreach ($candidates as $candidate) {
        if (file_exists($candidate)) {
            $_SERVER['SCRIPT_FILENAME'] = $candidate;
            $_SERVER['SCRIPT_NAME'] = rtrim($uri, '/') . '/index.php';
            chdir(dirname($candidate));
            require $candidate;
            return true;
        }
    }
}

// Try adding .php extension
if (file_exists($docRoot . $uri . '.php')) {
    $file = $docRoot . $uri . '.php';
    $_SERVER['SCRIPT_FILENAME'] = $file;
    chdir(dirname($file));
    require $file;
    return true;
}

// Try directory/index.php for clean URLs like /login -> /login/index.php
$phpFile = $docRoot . $uri . '/index.php';
if (file_exists($phpFile)) {
    $_SERVER['SCRIPT_FILENAME'] = $phpFile;
    chdir(dirname($phpFile));
    require $phpFile;
    return true;
}

// 404
http_response_code(404);
echo '<h1>404 Not Found</h1>';
echo '<p>The page <code>' . htmlspecialchars($uri) . '</code> was not found.</p>';
