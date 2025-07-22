<?php

namespace App\Domains\Hook;

use App\Models\Blog;

class BlogCustomCodeHookEvent
{

    public function __construct(
        private Blog $blog,
        private ?string $codeHead = null,
        private ?string $codeFoot = null
    ) {
    }

    public function getBlog(): Blog
    {
        return $this->blog;
    }

    public function appendCodeHead(string $code, string $prefix = "\n"): void
    {
        if ($this->codeHead === null) {
            $this->codeHead = '';
        }
        $this->codeHead .= $prefix . $code;
    }

    public function appendCodeFoot(string $code, string $prefix = "\n"): void
    {
        if ($this->codeFoot === null) {
            $this->codeFoot = '';
        }
        $this->codeFoot .= $prefix . $code;
    }

    public function getCodeHead(): ?string
    {
        return $this->codeHead;
    }

    public function getCodeFoot(): ?string
    {
        return $this->codeFoot;
    }

}