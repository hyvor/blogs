<?php

namespace App\Service\Export\Writer;

/**
 * Streams a single top-level JSON object to a local file, writing collections
 * in batches so callers never need to hold a full export in memory at once.
 */
class JsonFileWriter
{
    /** @var resource */
    private $handle;

    private bool $topLevelKeyWritten = false;
    private ?string $openCollectionKey = null;
    private bool $openCollectionHasItems = false;

    public function __construct(string $path)
    {
        $handle = fopen($path, 'w');
        if ($handle === false) {
            throw new \RuntimeException("Unable to open file for writing: {$path}");
        }
        $this->handle = $handle;
        $this->write('{');
    }

    public function value(string $key, mixed $data): void
    {
        $this->closeOpenCollection();
        $this->writeKey($key);
        $this->write(json_encode($data, JSON_THROW_ON_ERROR));
    }

    /**
     * Appends items to the array under $key. Safe to call repeatedly with the
     * same key to stream a collection in batches; call at least once with an
     * empty array to still emit `[]` for empty collections.
     *
     * @param mixed[] $items
     */
    public function collection(string $key, array $items): void
    {
        if ($this->openCollectionKey !== $key) {
            $this->closeOpenCollection();
            $this->writeKey($key);
            $this->write('[');
            $this->openCollectionKey = $key;
            $this->openCollectionHasItems = false;
        }

        foreach ($items as $item) {
            if ($this->openCollectionHasItems) {
                $this->write(',');
            }
            $this->write(json_encode($item, JSON_THROW_ON_ERROR));
            $this->openCollectionHasItems = true;
        }
    }

    public function end(): void
    {
        $this->closeOpenCollection();
        $this->write('}');
        fclose($this->handle);
    }

    private function writeKey(string $key): void
    {
        if ($this->topLevelKeyWritten) {
            $this->write(',');
        }
        $this->write(json_encode($key, JSON_THROW_ON_ERROR) . ':');
        $this->topLevelKeyWritten = true;
    }

    private function closeOpenCollection(): void
    {
        if ($this->openCollectionKey !== null) {
            $this->write(']');
            $this->openCollectionKey = null;
        }
    }

    private function write(string $data): void
    {
        fwrite($this->handle, $data);
    }
}
