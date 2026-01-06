<?php
// Generowanie hash dla hasła Lumine1!
require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\Hash;

$password = 'Lumine1!';
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "Hasło: $password\n";
echo "Hash: $hash\n";
echo "\nSkopiuj ten hash do skryptu SQL lub użyj w seedzie.\n";
