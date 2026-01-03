<?php
$zip = new ZipArchive();
$doc = __DIR__ . '/katalog_test.docx';
if ($zip->open($doc) !== true) { echo "open fail\n"; exit(1); }
for ($i=0;$i<$zip->numFiles;$i++) {
    printf("%03d: %s\n", $i, $zip->getNameIndex($i));
}
$zip->close();
