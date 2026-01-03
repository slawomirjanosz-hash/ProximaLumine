<?php
$zip = new ZipArchive();
if ($zip->open(__DIR__ . '/katalog_from_controller.docx') === true) {
    $index = $zip->locateName('word/document.xml');
    if ($zip->numFiles === 0) {
        echo "docx empty\n";
    }
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = $zip->getNameIndex($i);
        echo $name . "\n";
        if ($name === 'word/document.xml') {
            $content = $zip->getFromIndex($i);
            $pos = strpos($content, '<w:tbl');
            if ($pos !== false) {
                $snippet = substr($content, $pos, 2000);
                echo "\n--- TABLE SNIPPET ---\n" . htmlspecialchars(substr($snippet, 0, 2000)) . "\n";
            }
        }
    }
    $zip->close();
} else {
    echo "Failed to open docx\n";
}
