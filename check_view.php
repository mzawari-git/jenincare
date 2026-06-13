<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$finder = $app['view']->getFinder();
echo 'Hints: ' . json_encode($finder->getHints()) . "\n";
echo 'Paths: ' . json_encode($finder->getPaths()) . "\n";
$p = $finder->find('frontend.layouts.organic-spa.header');
echo 'Header: ' . $p . "\n";
echo 'musk: ' . (strpos(file_get_contents($p), 'musk-collection') !== false ? '1' : '0') . "\n";
echo 'teststr: ' . (strpos(file_get_contents($p), 'TEST_MUSK_12345') !== false ? '1' : '0') . "\n";
