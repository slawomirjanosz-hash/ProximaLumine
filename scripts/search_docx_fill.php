<?php
$zip=new ZipArchive();
if($zip->open(__DIR__.'/katalog_test.docx')===true){
    $s=$zip->getFromName('word/document.xml');
    if(strpos($s,'F3F4F6')!==false) echo "FOUND F3F4F6\n"; else echo "NOT FOUND F3F4F6\n";
    if(strpos($s,'4B5563')!==false) echo "FOUND 4B5563\n"; else echo "NOT FOUND 4B5563\n";
} else echo "OPEN FAIL\n";
