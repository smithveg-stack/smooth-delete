<?php
declare(strict_types=1);

/**
 * Minimal SQLite setup for the plain-PHP example. Creates the database
 * file and table on first run so the example works with zero setup.
 */

function smooth_delete_db(): PDO
{
    $dbPath = __DIR__ . '/records.sqlite';
    $isNew = !file_exists($dbPath);

    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($isNew) {
        $pdo->exec('CREATE TABLE records (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL)');
        $stmt = $pdo->prepare('INSERT INTO records (name) VALUES (:name)');
        foreach (['Ada Lovelace', 'Grace Hopper', 'Margaret Hamilton'] as $name) {
            $stmt->execute(['name' => $name]);
        }
    }

    return $pdo;
}
