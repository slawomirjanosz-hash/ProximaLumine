<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Bootstrap the application so Eloquent and facades work
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Part;

$parts = Part::with('category')->orderBy('name')->get();

$phpWord = new \PhpOffice\PhpWord\PhpWord();
$section = $phpWord->addSection();

// header: logo + company info
$logoPath = __DIR__ . '/../public/logo.png';
$header = $section->addHeader();
$headerTable = $header->addTable(['cellMargin' => 40]);
$headerTable->addRow();
if (file_exists($logoPath)) {
    $headerTable->addCell(1600, ['valign' => 'center'])->addImage($logoPath, ['height' => 34, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, 'marginTop' => 6]);
} else {
    $headerTable->addCell(1600, ['valign' => 'center']);
}
$companyCell = $headerTable->addCell(8000, ['valign' => 'center']);
$companyCell->addText('3C Automation sp. z o. o.', ['bold' => true, 'size' => 10], ['spaceAfter' => 0]);
$companyCell->addText('ul. Gliwicka 14, 44-167 Kleszczów', ['size' => 9], ['spaceAfter' => 0]);
$companyCell->addLink('mailto:biuro@3cautomation.eu', 'biuro@3cautomation.eu', ['size' => 9, 'color' => '4B5563'], ['spaceAfter' => 0]);

$tableStyle = [
    'borderSize' => 6,
    'borderColor' => 'CCCCCC',
    'cellMargin' => 80,
];
$phpWord->addTableStyle('PartsTable', $tableStyle);
$table = $section->addTable('PartsTable');

$cellStyleHeader = ['bgColor' => '4B5563'];
$headerFont = ['bold' => true, 'color' => 'FFFFFF'];
$table->addRow();
$table->addCell(4000, $cellStyleHeader)->addText('Nazwa', $headerFont);
$table->addCell(6000, $cellStyleHeader)->addText('Opis', $headerFont);
$table->addCell(4000, $cellStyleHeader)->addText('Kategoria', $headerFont, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
$table->addCell(2000, $cellStyleHeader)->addText('Stan', $headerFont, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

$rowIndex = 0;
foreach ($parts as $p) {
    $rowIndex++;
    $table->addRow();
    $cellStyle = ($rowIndex % 2 === 0) ? ['bgColor' => 'ECECEC'] : [];
    $table->addCell(4000, $cellStyle)->addText($p->name);
    $table->addCell(6000, $cellStyle)->addText($p->description ?? '-');
    $table->addCell(4000, $cellStyle)->addText($p->category->name ?? '-');
    $table->addCell(2000, $cellStyle)->addText((string)$p->quantity);
}

$out = __DIR__ . '/katalog_test.docx';
\PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007')->save($out);

echo "Saved to $out\n";