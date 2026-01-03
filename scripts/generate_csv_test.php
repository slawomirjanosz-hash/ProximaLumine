<?php
require __DIR__ . '/../vendor/autoload.php';
// bootstrap laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Part;
use App\Models\Category;

// Create a test part (without saving) to avoid DB modifications
$description = 'To jest testowy opis zawierający polskie znaki: ążśźćęół, oraz długie zdania aby przetestować łamanie linii w CSV. Powinno być łamane co 80 znaków bez łamania słów, czyli sprawdźmy to dokładnie.';
$part = new Part();
$part->name = 'Test Part';
$part->description = $description;
$part->quantity = 5;
$category = new Category();
$category->name = 'TestCat';
$part->setRelation('category', $category);

$file = __DIR__ . '/out_katalog.csv';
$out = fopen($file, 'w');
// BOM
fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
// sep
fwrite($out, "sep=;\r\n");
fputcsv($out, ['Nazwa', 'Opis', 'Kategoria', 'Stan'], ';');

$description = $part->description ? preg_replace('/\s+/', ' ', str_replace(["\r", "\n"], ' ', $part->description)) : '-';

fputcsv($out, [ $part->name, $description, $part->category->name ?? '-', $part->quantity ], ';');

fclose($out);

echo "Wrote: $file\n";

echo "--- RAW CONTENT ---\n";
echo file_get_contents($file);

// show positions of CRLF
$content = file_get_contents($file);
$pos = 0;
while (($p = strpos($content, "\r\n", $pos)) !== false) {
    echo "CRLF at: $p\n";
    $pos = $p+2;
}

?>