<?php
declare(strict_types=1);

final class Engine
{
    public function __construct(private array $nodes, private string $start = 'crank') {}

    /** Replay answers from the root; never trust a client-supplied node ID. */
    public function resolve(array $answers): array
    {
        $id = $this->start;
        $history = [];
        $seen = [];
        while (true) {
            if (isset($seen[$id]) || !isset($this->nodes[$id])) {
                throw new RuntimeException('Invalid diagnostic path.');
            }
            $seen[$id] = true;
            $node = $this->nodes[$id];
            if ($node['kind'] === 'result' || !array_key_exists($id, $answers)) {
                return ['id' => $id, 'node' => $node, 'history' => $history];
            }
            $answer = $answers[$id];
            if (!is_string($answer) || !isset($node['choices'][$answer])) {
                throw new InvalidArgumentException('Invalid answer.');
            }
            $history[] = ['question' => $node['body'], 'answer' => $answer];
            $id = $node['choices'][$answer];
        }
    }
}
