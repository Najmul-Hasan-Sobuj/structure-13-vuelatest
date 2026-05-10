<?php

namespace App\Ai;

use App\Ai\Agents\CustomerSupportAgent;

class CustomerSupportAssistant
{
    public function draftReply(string $customerQuestion): string
    {
        return CustomerSupportAgent::make()
            ->prompt($customerQuestion)
            ->text;
    }
}