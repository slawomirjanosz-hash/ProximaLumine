<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/magazyn/sprawdz/eksport-word', 'GET');
$response = $kernel->handle($request);
$body = $response->getContent();
$written = file_put_contents(__DIR__ . '/katalog.docx', $body);
$kernel->terminate($request, $response);
$size = $written !== false ? $written : 0;
echo "Status: " . $response->getStatusCode() . "\n";
echo "Content-Type: " . $response->headers->get('Content-Type') . "\n";
echo "Bytes written: " . $size . "\n";
