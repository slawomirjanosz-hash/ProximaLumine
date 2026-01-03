<?php
$zip = new ZipArchive();
if ($zip->open(__DIR__ . '/katalog_test.docx') === true) {
    $s = $zip->getFromName('word/document.xml');
    preg_match_all('/w:shd[^>]*w:fill="([A-Fa-f0-9]{3,6})"/', $s, $m);
    $fills = array_unique($m[1] ?? []);
    echo "Found fills: \n";
    foreach ($fills as $f) echo $f . "\n";
} else {
    echo "open fail\n";
}
