<?php
$zip = new ZipArchive();
$doc = __DIR__ . '/katalog_test.docx';
if ($zip->open($doc) !== true) { echo "open fail\n"; exit(1); }
$s = $zip->getFromName('word/document.xml');
echo substr($s, 0, 1200) . "\n";
$zip->close();
