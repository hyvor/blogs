<?php

namespace App\Api\Cli\Controller;

class UpdateFilesInput
{

    public bool $reset = false;

    /** @var array<string, string> */
    public array $files = [];

}