$f = "d:\Programowanie\Laravel\ProximaLumine\resources\views\parts\project-details.blade.php"
$c = [IO.File]::ReadAllText($f)

# 1. Remove "Najpierw dodaj grupę przyciskiem" visible hint line (the <p> tag)
$hint1Start = $c.IndexOf('<p class="text-xs text-gray-500 mt-2">Najpierw dodaj grup')
if ($hint1Start -ge 0) {
    $hint1End = $c.IndexOf('</p>', $hint1Start) + 4
    # also remove trailing CRLF
    if ($c.Substring($hint1End, 2) -eq "`r`n") { $hint1End += 2 }
    $c = $c.Substring(0, $hint1Start) + $c.Substring($hint1End)
    Write-Host "Removed hint1 (Najpierw dodaj grupę)"
} else {
    Write-Host "hint1 not found"
}

# 2. Remove "Wyszukiwarka aktywuje się" hint wrapped in @if block
$hint2Pattern = '@if(empty($importedCostRows ?? []))' + "`r`n"
$hint2Start = $c.IndexOf($hint2Pattern + '                    <p class="mt-1 text-xs text-gray-500">Wyszukiwarka aktywuje')
if ($hint2Start -lt 0) {
    # Try alternate form without @if wrapper
    $hint2Start = $c.IndexOf('<p class="mt-1 text-xs text-gray-500">Wyszukiwarka aktywuje')
    if ($hint2Start -ge 0) {
        $hint2End = $c.IndexOf('</p>', $hint2Start) + 4
        if ($c.Substring($hint2End, 2) -eq "`r`n") { $hint2End += 2 }
        $c = $c.Substring(0, $hint2Start) + $c.Substring($hint2End)
        Write-Host "Removed hint2 bare (Wyszukiwarka)"
    } else {
        Write-Host "hint2 not found"
    }
} else {
    # Find whole @if block
    $blockEnd = $c.IndexOf('@endif', $hint2Start) + 6
    if ($c.Substring($blockEnd, 2) -eq "`r`n") { $blockEnd += 2 }
    $c = $c.Substring(0, $hint2Start) + $c.Substring($blockEnd)
    Write-Host "Removed hint2 block (Wyszukiwarka)"
}

[IO.File]::WriteAllText($f, $c, [System.Text.UTF8Encoding]::new($false))
Write-Host "Done. File size: $($c.Length)"
