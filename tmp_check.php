$lines = file('resources/views/offers-edit.blade.php'); foreach ([235, 649, 940, 1473] as $lineNum) { $line = $lines[$lineNum - 1]; echo "Line $lineNum: " . trim($line) . "\n"; }
