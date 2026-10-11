<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$hash = Hash::make('admin@123');
$count = User::query()->update(['password' => $hash]);

echo "Successfully updated {$count} users passwords to admin@123\n\n";
echo "Available Test Accounts:\n";
echo "====================================\n";

$users = User::all(['id', 'name', 'email', 'role']);
foreach ($users as $user) {
    $roleName = $user->role === 1 ? 'ADMIN' : 'USER';
    echo "[{$roleName}] Email: {$user->email} | Password: admin@123 | Name: {$user->name}\n";
}
