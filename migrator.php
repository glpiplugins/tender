
<?php

use Illuminate\Database\Migrations\Migrator;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Database\ConnectionResolver;

// Pfad zu deinen Migrationsdateien
$migrationsPath = __DIR__ . '/migrations';

// Filesystem- und Repository-Instanzen erstellen
$files = new Filesystem;
$repository = new DatabaseMigrationRepository($capsule->getDatabaseManager(), 'migrations');

// Migration-Repository initialisieren, falls noch nicht geschehen
if (!$repository->repositoryExists()) {
    $repository->createRepository();
}

// Migrator-Instanz erstellen
$migrator = new Migrator($repository, new ConnectionResolver(['default' => $capsule->getDatabaseManager()]), $files);

// Migrations ausführen
if ($migrator->needsToRun()) {
    $migrator->run($migrationsPath);
}