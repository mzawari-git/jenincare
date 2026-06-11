<?php

define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Fix subdirectory base URL detection: mod_rewrite changes SCRIPT_NAME but not REQUEST_URI.
// When /jenincare/ is rewritten to /jenincare/public/, the Apache-internal path is used for
// SCRIPT_NAME but REQUEST_URI stays as the original request. The mismatch breaks Symfony's
// base URL detection, causing pathInfo to be the full URI instead of just /.
// Our fix: use a custom request class that overrides getPathInfo() so it's always correct,
// even after duplicate() (which is called by CompiledRouteCollection::requestWithoutTrailingSlash).
use App\Overrides\PatchedRequest;

$request = PatchedRequest::capture();

// Determine the correct base URL from SCRIPT_NAME
$scriptName = $_SERVER['SCRIPT_NAME']; // e.g. /jenincare/public/index.php
$baseUrl = rtrim(dirname($scriptName), '/\\'); // e.g. /jenincare/public
$request->setBaseUrl($baseUrl);

// Derive pathInfo from REQUEST_URI minus baseUrl
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$pos = strpos($requestUri, '?');
if ($pos !== false) {
    $requestUri = substr($requestUri, 0, $pos);
}
$prefixLength = strlen($baseUrl);
$pathInfo = substr($requestUri, $prefixLength);
if ($pathInfo === false || $pathInfo === '') {
    $pathInfo = '/';
} elseif ($pathInfo[0] !== '/') {
    $pathInfo = '/' . $pathInfo;
}
$request->setPathInfo($pathInfo);

$response = $kernel->handle($request);

$response->send();

$kernel->terminate($request, $response);
