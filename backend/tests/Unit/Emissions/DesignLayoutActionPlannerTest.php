<?php

namespace Tests\Unit\Emissions;

use App\Services\Emissions\DesignAssistant\DesignLayoutActionPlanner;
use PaqSuite\LaravelCore\ChatAssistant\Contracts\LlmChatCompletionClient;
use PaqSuite\LaravelCore\Llm\LlmCredentialContext;
use PHPUnit\Framework\TestCase;

final class DesignLayoutActionPlannerTest extends TestCase
{
    public function test_heuristics_group_and_sum(): void
    {
        $planner = new DesignLayoutActionPlanner($this->unusedLlm());
        $result = $planner->fromHeuristics('agrupá por cliente y sumá los minutos');

        $types = array_column($result['actions'], 'action');
        $this->assertContains('addGroupByField', $types);
        $this->assertContains('addGroupSum', $types);
        $group = null;
        foreach ($result['actions'] as $action) {
            if ($action['action'] === 'addGroupByField') {
                $group = $action;
            }
        }
        $this->assertSame('clienteNombre', $group['payload']['field'] ?? null);
    }

    public function test_sanitize_drops_unknown_field(): void
    {
        $planner = new DesignLayoutActionPlanner($this->unusedLlm());
        $out = $planner->sanitizeActions([
            [
                'action' => 'addGroupByField',
                'payload' => ['field' => 'rentabilidad'],
                'resultado' => 'ok',
            ],
            [
                'action' => 'setReportTitle',
                'payload' => ['title' => 'Ok'],
                'resultado' => 'ok',
            ],
        ]);

        $this->assertCount(1, $out);
        $this->assertSame('setReportTitle', $out[0]['action']);
    }

    private function unusedLlm(): LlmChatCompletionClient
    {
        return new class implements LlmChatCompletionClient {
            public function complete(LlmCredentialContext $credential, array $messages, array $images = []): string
            {
                return '{}';
            }
        };
    }
}
