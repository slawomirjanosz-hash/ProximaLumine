<?php
$zip=new ZipArchive();
if($zip->open(__DIR__.'/katalog_test.docx')===true){
    $s=$zip->getFromName('word/document.xml');
    echo (strpos($s,'<w:hdr')!==false) ? "HAS HDR\n" : "NO HDR\n";
} else echo "OPEN FAIL\n";
