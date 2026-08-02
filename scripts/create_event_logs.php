<?php
// Quick table creation script
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;

if (!Schema::hasTable('event_logs')) {
    Schema::create('event_logs', function ($t) {
        $t->id();
        $t->string('entity_type')->nullable();
        $t->unsignedBigInteger('entity_id')->nullable();
        $t->string('event_key');
        $t->string('event_class')->nullable();
        $t->unsignedBigInteger('actor_id')->nullable();
        $t->unsignedBigInteger('branch_id')->nullable();
        $t->json('metadata')->nullable();
        $t->timestamp('occurred_at')->useCurrent();
    });
    echo "event_logs CREATED\n";
} else {
    echo "event_logs EXISTS\n";
}
