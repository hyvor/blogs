<?php declare(strict_types=1);

namespace App\Service\CodeHighlight;

class Annotations
{
    private bool|null $numbers = null;

    /** @var array<string, int|false> */
    private array $renumbers = [];

    /** @var int[] */
    private array $highlightLines = [];

    /** @var int[] */
    private array $focusLines = [];

    /** @var int[] */
    private array $diffAddLines = [];

    /** @var int[] */
    private array $diffRemoveLines = [];

    private bool $hasError = false;

    public function __construct(string $annotations)
    {
        $annotations = preg_split('/\s+/', $annotations) ?: [];

        try {
            foreach ($annotations as $annotation) {
                $this->processAnnotation($annotation);
            }
        } catch (\Exception) {
            $this->hasError = true;
        }
    }

    private function processAnnotation(string $annotation): void
    {
        $split = explode('=', $annotation);
        $key = $split[0];
        $value = $split[1] ?? null;

        if ($key === '' || $value === null) {
            return;
        }

        if (in_array($key, ['h', 'f', '+', '-'])) {
            $lines = [];
            foreach (explode(',', $value) as $range) {
                array_push($lines, ...$this->processRange($range));
            }

            match ($key) {
                'h' => $this->highlightLines = $lines,
                'f' => $this->focusLines = $lines,
                '+' => $this->diffAddLines = $lines,
                '-' => $this->diffRemoveLines = $lines,
            };
        } elseif ($key === 'numbers') {
            $this->numbers = $value === 'true' ? true : ($value === 'false' ? false : null);
        } elseif ($key === 'renumber') {
            foreach (explode(',', $value) as $renumber) {
                $parts = explode(':', $renumber);
                $from = $parts[0];
                $to = ($parts[1] ?? '') === 'null' ? false : (int) ($parts[1] ?? 0);
                $this->renumbers[$from] = $to;
            }
        }
    }

    /** @return int[] */
    private function processRange(string $range): array
    {
        $lines = [];
        if (preg_match('/^(\d+)(?:-(\d+))?$/', $range, $matches)) {
            $start = (int) $matches[1];
            if (!isset($matches[2])) {
                $lines[] = $start;
            } else {
                $end = (int) $matches[2];
                if ($end < $start) {
                    [$start, $end] = [$end, $start];
                }
                for ($i = $start; $i <= $end; $i++) {
                    $lines[] = $i;
                }
            }
        }
        return $lines;
    }

    public function hasHighlight(): bool { return count($this->highlightLines) > 0; }
    public function hasFocus(): bool { return count($this->focusLines) > 0; }
    public function hasDiffAdd(): bool { return count($this->diffAddLines) > 0; }
    public function hasDiffRemove(): bool { return count($this->diffRemoveLines) > 0; }
    public function shouldHighlight(int $lineNumber): bool { return in_array($lineNumber, $this->highlightLines); }
    public function shouldFocus(int $lineNumber): bool { return in_array($lineNumber, $this->focusLines); }
    public function shouldDiffAdd(int $lineNumber): bool { return in_array($lineNumber, $this->diffAddLines); }
    public function shouldDiffRemove(int $lineNumber): bool { return in_array($lineNumber, $this->diffRemoveLines); }
    public function hasLineNumbers(): bool { return $this->numbers !== false; }
    public function getMaxLineNumber(): int
    {
        if (!count($this->renumbers)) {
            return -1;
        }
        $max = max($this->renumbers);
        return $max === false ? -1 : $max;
    }
    public function getRenumberedLineNumber(int $realLineNumber, int $orElse): int|false
    {
        $key = (string) $realLineNumber;
        return $this->renumbers[$key] ?? $orElse;
    }
    public function hasError(): bool { return $this->hasError; }
}
