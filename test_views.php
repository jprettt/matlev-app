<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::first();
if (!$user) {
    $user = App\Models\User::create([
        'name' => 'Ahmad Fauzi (PLN User)',
        'email' => 'ahmad.fauzi@pln.co.id',
        'password' => bcrypt('password123'),
        'role' => 'user'
    ]);
}

auth()->login($user);

$ctrl = new App\Http\Controllers\MatlevController();
$ref = new ReflectionMethod($ctrl, 'getStatsAndData');
$ref->setAccessible(true);
$data = $ref->invoke($ctrl);

$views = ['user.dashboard', 'user.kriteria', 'user.revisi', 'user.riwayat', 'user.panduan'];

foreach ($views as $v) {
    try {
        $html = view($v, $data)->render();
        echo "View [{$v}]: SUCCESS (Length: " . strlen($html) . " bytes)\n";
    } catch (\Throwable $e) {
        echo "View [{$v}]: ERROR - " . $e->getMessage() . "\n";
    }
}
