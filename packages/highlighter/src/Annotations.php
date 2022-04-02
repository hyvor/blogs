<?php
namespace Hyvor\SyntaxHighlighter;

class Annotations
{

    public bool|null $numbers = null;
    public array $renumbers = [];
    public array $highlightLines = [];
    public array $diffAddLines = [];
    public array $diffRemoveLines = [];

    public function __construct(string $annotations)
    {

        $annotations = preg_split('/\s+/', $annotations);        

        foreach ($annotations as $annotation) {
            $this->processAnnotation($annotation);
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


        if (in_array($key, ['highlight', 'focus', '+', '-'])) {

            $lines = [];
            $ranges = explode(',', $value);

            foreach ($ranges as $range) {
                array_push($lines, ...$this->processRange($range));
            }

            if ($key === 'highlight') {
                $this->highlightLines = $lines;
            } else if ($key === 'focus') {
                $this->focusLines = $lines;
            } else if ($key === '+') {
                $this->diffAddLines = $lines;
            } else if ($key === '-') {
                $this->diffRemoveLines = $lines;
            }

        } else if ($key === 'numbers') {

            if ($value === 'true') {
                $this->numbers = true;
            } else if ($value === 'false') {
                $this->numbers = false;
            }

        } else if ($key === 'renumber') {



        }

    }

    private function processRange($range)
    {   

        $lines = [];

        if (preg_match('/^(\d+)(?:-(\d+))?$/', $range, $matches)) {

            $start = (int) $matches[1];

            if (!isset($matches[2])) {

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

    public function hasHighlight()
    {
        return count($this->highlightLines) > 0;
    }

    public function hasFocus()
    {
        return count($this->focusLines) > 0;
    }

    public function hasDiffAdd()
    {
        return count($this->diffAddLines) > 0;
    }

    public function hasDiffRemove()
    {
        return count($this->diffRemoveLines) > 0;
    }

}