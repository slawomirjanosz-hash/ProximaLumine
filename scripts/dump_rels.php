<?php
$zip = new ZipArchive();
if ($zip->open(__DIR__ . '/katalog_test.docx') === true) {
    $rels = $zip->getFromName('word/_rels/document.xml.rels');
    echo $rels ?: 'no rels';
} else {
    echo "open fail\n";
}
