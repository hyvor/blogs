<?php

namespace App\Service\Ai\Agent\Tool;

use App\Service\Ai\Agent\Tool\DocumentOps\DocumentOpsTool;
use Symfony\AI\Platform\Result\ResultInterface;

readonly class AgentCallResult
{

    public function __construct(
        private ResultInterface $result,
        private DocumentOpsTool $documentOpsTool,
    ) {}

    public function getResult(): ResultInterface
    {
        return $this->result;
    }

    public function getDocumentOpsTool(): DocumentOpsTool
    {
        return $this->documentOpsTool;
    }

}
