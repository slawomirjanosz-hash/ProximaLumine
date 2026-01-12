<?php
$zip = new ZipArchive();
$doc = __DIR__ . '/katalog_test.docx';
if ($zip->open($doc) !== true) { echo "open fail\n"; exit(1); }
$header = $zip->getFromName('word/header1.xml');
if ($header === false) { echo "no header\n"; exit(0); }
// print a snippet around company name
$pos = strpos($header, 'Moja Firma');
if ($pos !== false) {
    echo "Found company text. Snippet:\n" . substr($header, max(0, $pos-200), 400) . "\n";
} else {
    echo "Company text not found in header\n";
}
$zip->close();
