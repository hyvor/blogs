<?php

namespace App\Service\CodeHighlight;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class Highlighter
{
    public function getAllThemes(): mixed
    {
        return $this->callJs(['type' => 'themes']);
    }

    /**
     * @param array<string, mixed> $arguments
     */
    private function callJs(array $arguments): mixed
    {
        $command = [
            (new ExecutableFinder())->find('node', 'node', [
                '/usr/local/bin',
                '/opt/homebrew/bin',
            ]),
            'index.js',
            json_encode($arguments),
        ];

        $process = new Process(
            command: $command,
            cwd: (string)realpath(dirname(__DIR__, 3) . '/js'),
            timeout: null,
        );

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return json_decode($process->getOutput());
    }
}
