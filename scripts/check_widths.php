<?php
$docx = __DIR__ . '/katalog_from_controller.docx';
$zip = new ZipArchive();
$zip->open($docx);
$xml = $zip->getFromName('word/document.xml');
$zip->close();

// Extract gridCol widths
preg_match_all('/<w:gridCol w:w="(\d+)"/', $xml, $matches);
echo "Grid columns widths:\n";
foreach ($matches[1] as $i => $width) {
    $cols = ['Nazwa', 'Opis', 'Kategoria', 'Stan'];
    echo ($cols[$i] ?? "Col$i") . ": $width dxa\n";
}
?>
