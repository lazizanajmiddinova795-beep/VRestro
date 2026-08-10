<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'admin@itcloud.uz')->first();
if ($user) {
    echo "Current Roles: " . json_encode($user->roles->pluck('name')) . "\n";
    $user->assignRole('Manager');
    echo "Assigned Manager role.\n";
} else {
    echo "User not found\n";
}
