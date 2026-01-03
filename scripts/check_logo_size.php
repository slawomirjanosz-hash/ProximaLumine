<?php
$docx = __DIR__ . '/katalog_from_controller.docx';
$zip = new ZipArchive();
if ($zip->open($docx) !== true) { echo "Failed to open $docx\n"; exit(1); }
$names = [];
for ($i=0;$i<$zip->numFiles;$i++) { $names[] = $zip->getNameIndex($i); }
echo implode("\n", $names), "\n";
$imgName = null;
foreach ($names as $n) { if (strpos($n, 'word/media/') === 0 && preg_match('/section_image/', $n)) { $imgName = $n; break; } }
if (!$imgName) { echo "No section image found\n"; exit(1); }
echo "Found image: $imgName\n";
$img = $zip->getFromName($imgName);
$out = __DIR__ . '/extracted_logo.png';
file_put_contents($out, $img);
$info = getimagesize($out);
if ($info) {
    echo "Image size: " . $info[0] . "x" . $info[1] . " (mime: " . $info['mime'] . ")\n";
} else {
    echo "Could not determine image size\n";
}
$zip->close();
