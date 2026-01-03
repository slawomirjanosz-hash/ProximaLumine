<?php
$file = __DIR__ . '/out_katalog.csv';
if (!file_exists($file)) { echo "File not found: $file\n"; exit(1); }
$content = file_get_contents($file);
$len = strlen($content);
$show = min(400, $len);
for ($i=0;$i<$show;$i++){
    $byte = ord($content[$i]);
    printf("%04d: %02X %s\n", $i, $byte, preg_replace('/[^\x20-\x7E]/','.', $content[$i]));
}

// Find NBSP sequences
$pos = 0; $found=false;
while (($p = strpos($content, "\xC2\xA0", $pos)) !== false) {
    echo "NBSP sequence found at byte position: $p\n";
    $found=true;
    $pos = $p + 2;
}
if (!$found) echo "No NBSP sequence found.\n";

?>