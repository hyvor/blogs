<?php

namespace Hyvor\SyntaxHighlighter;

class Annotations
{
    private bool|null $numbers = null;

    private array $renumbers = [];

    private array $highlightLines = [];

    private array $focusLines = [];

    private array $diffAddLines = [];

    private array $diffRemoveLines = [];

    private bool $hasError = false;

    public function __construct(string $annotations)
    {
        $annotations = preg_split('/\s+/', $annotations);

        try {
            foreach ($annotations as $annotation) {
                $this->processAnnotation($annotation);
            }
        } catch (\Exception) {
            $this->hasError = true;
        }
    }

    private function processAnnotation(string $annotation)
    {
        $split = explode('=', $annotation);

        $key = $split[0] ?? null;
        $value = $split[1] ?? null;

        if ($key == null || $value === null) {
            return;
        }

        if (in_array($key, ['h', 'f', '+', '-'])) {
            $lines = [];
            $ranges = explode(',', $value);

            foreach ($ranges as $range) {
                array_push($lines, ...$this->processRange($range));
            }

            if ($key === 'h') {
                $this->highlightLines = $lines;
            } elseif ($key === 'f') {
                $this->focusLines = $lines;
            } elseif ($key === '+') {
                $this->diffAddLines = $lines;
            } elseif ($key === '-') {
                $this->diffRemoveLines = $lines;
            }
        } elseif ($key === 'numbers') {
            if ($value === 'true') {
                $this->numbers = true;
            } elseif ($value === 'false') {
                $this->numbers = false;
            }
        } elseif ($key === 'renumber') {
            $renumbers = explode(',', $value);

            foreach ($renumbers as $renumber) {
                $split = explode(':', $renumber);

                $from = $split[0];
                $to = $split[1] === 'null' ? false : (int) $split[1];

                $this->renumbers[$from] = $to;
            }
        }
    }

    private function processRange($range)
    {
        $lines = [];

        if (preg_match('/^(\d+)(?:-(\d+))?$/', $range, $matches)) {
            $start = (int) $matches[1];

            if (! isset($matches[2])) {

                // single number
                $lines[] = (int) $start;
            } else {

                // range
                $end = (int) $matches[2];

                if ($end < $start) {
                    $start = $end;
                    $end = $start;
                }

                for ($i = $start; $i <= $end; $i++) {
                    $lines[] = $i;
                }
            }
        }

        return $lines;
    }

    public function hasHighlight(): bool
    {
        return count($this->highlightLines) > 0;
    }

    public function hasFocus(): bool
    {
        return count($this->focusLines) > 0;
    }

    public function hasDiffAdd(): bool
    {
        return count($this->diffAddLines) > 0;
    }

    public function hasDiffRemove(): bool
    {
        return count($this->diffRemoveLines) > 0;
    }

    public function shouldHighlight(int $lineNumber): bool
    {
        return in_array($lineNumber, $this->highlightLines);
    }

    public function shouldFocus(int $lineNumber): bool
    {
        return in_array($lineNumber, $this->focusLines);
    }

    public function shouldDiffAdd(int $lineNumber): bool
    {
        return in_array($lineNumber, $this->diffAddLines);
    }

    public function shouldDiffRemove(int $lineNumber): bool
    {
        return in_array($lineNumber, $this->diffRemoveLines);
    }

    public function hasLineNumbers(): bool
    {
        return $this->numbers !== false;
    }

    public function getMaxLineNumber(): int
    {
        return count($this->renumbers) ? max($this->renumbers) : -1;
    }

    public function getRenumberedLineNumber(int $realLineNumber, int $orElseLineNumber): int
    {
        return $this->renumbers[$realLineNumber] ?? $orElseLineNumber;
    }

    public function hasError(): bool
    {
        return $this->hasError;
    }
}
