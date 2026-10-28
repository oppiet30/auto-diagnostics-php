<?php
declare(strict_types=1);
ini_set('session.use_strict_mode', '1');
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
session_start();
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
$_SESSION['answers'] ??= [];
function h(string $value): string { return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
try {
    require __DIR__ . '/../app/bootstrap.php';
    $state = $engine->resolve($_SESSION['answers']);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!is_string($_POST['csrf'] ?? null) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
            http_response_code(403);
            exit('Invalid form token. Reload the page.');
        }
        if (($_POST['action'] ?? '') === 'restart') {
            $_SESSION['answers'] = [];
        } else {
            $answer = $_POST['answer'] ?? null;
            if (($_POST['question'] ?? '') !== $state['id'] || $state['node']['kind'] !== 'question' || !is_string($answer) || !isset($state['node']['choices'][$answer])) {
                http_response_code(400);
                exit('Invalid or outdated answer. Reload the questionnaire.');
            }
            $_SESSION['answers'][$state['id']] = $answer;
        }
        header('Location: ./', true, 303);
        exit;
    }
} catch (Throwable $error) {
    error_log((string) $error);
    http_response_code(503);
    exit('Diagnostics unavailable. Check database configuration and import database/schema.sql and database/seed.sql.');
}
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Auto Diagnostics</title><link rel="stylesheet" href="style.css"></head>
<body><main><p class="eyebrow">Auto Diagnostics · starter edition</p><h1>Engine won’t crank</h1>
<p>For conventional 12-volt starting systems. Hybrid and EV high-voltage systems are outside this guide.</p>
<aside>Park safely, apply the parking brake, and keep clear of moving parts. Stop for smoke, a burning smell, leaking fuel, or a damaged battery. Never bridge starter terminals or bypass safety switches.</aside>
<section><h2><?= $state['node']['kind'] === 'result' ? 'Possible cause and next check' : 'Next question' ?></h2>
<p class="question"><?= h($state['node']['body']) ?></p>
<?php if ($state['node']['kind'] === 'question'): ?>
<details><summary>Why are you asking?</summary><p><?= h($state['node']['explanation']) ?></p></details>
<form method="post"><input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>"><input type="hidden" name="question" value="<?= h($state['id']) ?>">
<?php foreach ($state['node']['choices'] as $answer => $next): ?><button name="answer" value="<?= h($answer) ?>"><?= h(ucfirst($answer)) ?></button><?php endforeach; ?></form>
<?php else: ?><p><?= h($state['node']['explanation']) ?></p><p>This is a troubleshooting lead, not a confirmed failed part. Verify before replacing components.</p><?php endif; ?></section>
<?php if ($state['history']): ?><section><h2>Your answers</h2><ol><?php foreach ($state['history'] as $entry): ?><li><?= h($entry['question']) ?> <strong><?= h($entry['answer']) ?></strong></li><?php endforeach; ?></ol></section><?php endif; ?>
<form method="post"><input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>"><button name="action" value="restart">Start over</button></form>
<footer>Educational rules · no AI service or scan tool required.</footer></main></body></html>
