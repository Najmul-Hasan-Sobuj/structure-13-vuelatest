<?php

namespace Tests\Feature\Ai;

use Tests\TestCase;
use App\Ai\Agents\CustomerSupportAgent;
use App\Ai\CustomerSupportAssistant;

class CustomerSupportAssistantTest extends TestCase
{
    public function test_it_drafts_a_support_reply_with_the_ai_agent(): void
    {
        CustomerSupportAgent::fake(['Please check the tracking link in your order confirmation email.']);

        $assistant = app(CustomerSupportAssistant::class);

        $reply = $assistant->draftReply('Where is my order?');

        $this->assertSame('Please check the tracking link in your order confirmation email.', $reply);

        CustomerSupportAgent::assertPrompted('Where is my order?');
    }
}
