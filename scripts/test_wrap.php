<?php
$s = 'To jest testowy opis zawierający polskie znaki: ążśźćęół, oraz długie zdania aby przetestować łamanie linii w CSV. Powinno być łamane co 80 znaków bez łamania słów, czyli sprawdźmy to dokładnie.';
$w = wordwrap($s, 80, "\n", false);
$w2 = str_replace("\n", "\r\n", $w);
echo $w2 . PHP_EOL;
echo '--- POSITIONS ---' . PHP_EOL;
$pos = 0;
while (($p = strpos($w2, "\r\n", $pos)) !== false) {
    echo $p . PHP_EOL;
    $pos = $p + 2;
}

// Also show byte offsets and raw bytes around each break
$bytes = strlen($w2);
echo "Total bytes: $bytes" . PHP_EOL;
for ($i = 0; $i < strlen($w2); $i++) {
    $ch = $w2[$i];
    $ord = ord($ch);
    if ($i < 200 || $i > strlen($w2)-200) {
        // print only first and last parts to avoid flooding
    }
}

?>