<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$u = App\Models\EvidenceUpload::first();
if (! $u) {
    echo "NO_UPLOADS\n";
    exit(0);
}
$val = $u->uploaded_at;
if (is_null($val)) {
    echo "NULL\n";
} else {
    echo get_class($val) . " => " . $val->format('d-m-Y H:i') . "\n";
}
