<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\PartController;
use Illuminate\Http\Request;

$ctrl = new PartController();
$response = $ctrl->exportWord(new Request());

// If BinaryFileResponse or StreamedResponse
if (method_exists($response, 'getFile')) {
    $file = $response->getFile();
    $path = $file->getPathname();
    copy($path, __DIR__ . '/katalog_from_controller.docx');
    echo "Copied file from controller: $path -> scripts/katalog_from_controller.docx\n";
} else {
    echo "Response class: " . get_class($response) . "\n";
}
