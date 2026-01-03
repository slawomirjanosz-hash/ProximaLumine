<?php
require __DIR__ . '/../vendor/autoload.php';
// bootstrap laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Exports\PartsExport;
use App\Models\Part;
use App\Models\Category;
use Maatwebsite\Excel\Excel;

// Create a test part
$description = 'To jest testowy opis zawierający polskie znaki: ążśźćęół, oraz długie zdania aby przetestować łamanie linii w CSV i XLSX. Powinno być widoczne w jednej komórce.';
$part = new Part();
$part->name = 'Test Part';
$part->description = $description;
$part->quantity = 5;
$category = new Category();
$category->name = 'TestCat';
$part->setRelation('category', $category);

$export = new PartsExport(collect([$part]));
$file = __DIR__ . '/out_katalog.xlsx';

// Use the Excel writer to store the file
try {
        $content = \Maatwebsite\Excel\Facades\Excel::raw($export, Excel::XLSX);
        file_put_contents($file, $content);
        echo "Wrote: $file\n";
    } catch (\Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }

// Inspect resulting file column width using PhpSpreadsheet directly
if (file_exists($file)) {
    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
    $sheet = $reader->getActiveSheet();
    $width = $sheet->getColumnDimension('B')->getWidth();
    echo "Column B width in file: " . var_export($width, true) . "\n";
} else {
    echo "File not found: $file\n";
}

?>