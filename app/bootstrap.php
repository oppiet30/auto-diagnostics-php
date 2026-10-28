<?php
declare(strict_types=1);
require_once __DIR__ . '/Engine.php';
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO($config['dsn'], $config['username'], $config['password'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);
$query = $pdo->prepare('SELECT id, kind, body, explanation FROM diagnostic_nodes WHERE topic = ?');
$query->execute(['no-crank']);
$nodes = [];
foreach ($query as $row) {
    $nodes[$row['id']] = $row + ['choices' => []];
}
$query = $pdo->prepare('SELECT question_id, answer, next_id FROM diagnostic_choices WHERE topic = ? ORDER BY position');
$query->execute(['no-crank']);
foreach ($query as $row) {
    $nodes[$row['question_id']]['choices'][$row['answer']] = $row['next_id'];
}
$engine = new Engine($nodes);
