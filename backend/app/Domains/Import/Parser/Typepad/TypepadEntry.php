<?php

namespace App\Domains\Import\Parser\Typepad;

class TypepadEntry
{

    /**
     * @var array<string, array<string>>
     */
    private array $values = [];

    public function __construct(string $entryString)
    {
        $subsections = explode("-----\n", $entryString);
        $metadataSection = $subsections[0] ?? '';
        $this->parseMetadata($metadataSection);

        $multilineSections = array_slice($subsections, 1);
        $this->parseMultiline($multilineSections);
    }

    private function parseMetadata(string $metadataSection): void
    {
        $lines = explode("\n", $metadataSection);
        foreach ($lines as $line) {
            $parts = explode(": ", $line, 2);
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $value = trim($parts[1]);
                $this->values[$key][] = $value;
            }
        }
    }

    private function parseMultiline(array $multilineSections): void
    {
        foreach ($multilineSections as $section) {
            $firstLine = strtok($section, "\n");
            $key = rtrim($firstLine, ":");
            $nextLines = substr($section, strlen($firstLine) + 1);
            $this->values[$key][] = trim($nextLines);
        }
    }

    public function getString(string $key): ?string
    {
        $values = $this->values[$key] ?? [];
        return $values[0] ?? null;
    }

    /**
     * @return string[]
     */
    public function getStrings(string $key): array
    {
        return $this->values[$key] ?? [];
    }

}
