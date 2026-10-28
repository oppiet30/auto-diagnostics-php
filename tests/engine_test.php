<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
$count = 0;
$walk = function (array $answers, array $visited) use (&$walk, &$count, $engine): void {
    $state = $engine->resolve($answers);
    if (isset($visited[$state['id']])) { throw new RuntimeException('Cycle detected'); }
    $visited[$state['id']] = true;
    if ($state['node']['kind'] === 'result') { $count++; return; }
    if (array_keys($state['node']['choices']) !== ['yes', 'no', 'unknown']) { throw new RuntimeException('Missing answer option'); }
    foreach ($state['node']['choices'] as $answer => $next) {
        $walk($answers + [$state['id'] => $answer], $visited);
    }
};
$walk([], []);
$expected = [
    [['crank' => 'yes'], 'other'],
    [['crank' => 'no', 'lights' => 'no'], 'power'],
    [['crank' => 'no', 'lights' => 'yes', 'clicks' => 'yes'], 'supply'],
    [['crank' => 'unknown'], 'inspection'],
];
foreach ($expected as [$answers, $id]) {
    if ($engine->resolve($answers)['id'] !== $id) { throw new RuntimeException('Wrong conclusion'); }
}
try { $engine->resolve(['crank' => 'invalid']); throw new RuntimeException('Accepted invalid answer'); }
catch (InvalidArgumentException $expected) {}
if ($engine->resolve(['lights' => 'no'])['id'] !== 'crank') { throw new RuntimeException('Accepted skipped question'); }
echo "PASS: $count terminal paths, known outcomes, invalid answers and skipped questions\n";
