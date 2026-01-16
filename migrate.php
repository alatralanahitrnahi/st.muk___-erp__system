#!/usr/bin/env php
<?php

define('LARAVEL_START', microtime(true));

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "✅ Laravel bootstrapped successfully\n\n";

// Run migrations
echo "🔄 Running migrations...\n";

try {
    $migrator = $app->make('migrator');
    $migrator->setConnection('sqlite');
    
    // Get migration files
    $paths = [$app->databasePath().'/migrations'];
    
    // Run migrations
    $migrator->run($paths);
    
    $notes = $migrator->getNotes();
    foreach ($notes as $note) {
        echo "  $note\n";
    }
    
    echo "\n✅ Migrations completed successfully!\n";
    
} catch (Exception $e) {
    echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
