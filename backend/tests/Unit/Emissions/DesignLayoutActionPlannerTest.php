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

    /**
     * @dataProvider titleMessageProvider
     */
    public function test_heuristics_extract_full_title(string $message, string $expectedTitle): void
    {
        $planner = new DesignLayoutActionPlanner($this->unusedLlm());
        $result = $planner->fromHeuristics($message);

        $titleAction = null;
        foreach ($result['actions'] as $action) {
            if ($action['action'] === 'setReportTitle') {
                $titleAction = $action;
            }
        }

        $this->assertNotNull($titleAction, 'Debe mapear setReportTitle');
        $this->assertSame($expectedTitle, $titleAction['payload']['title'] ?? null);
    }

    /**
     * @return list<array{0: string, 1: string}>
     */
    public static function titleMessageProvider(): array
    {
        return [
            ['cambia el título a consulta detallada', 'consulta detallada'],
            ['cambia el título a "consulta detallada"', 'consulta detallada'],
            ['pone el título como Informe PAQ', 'Informe PAQ'],
            ['título: Demo Partes', 'Demo Partes'],
            ['Cambiá el título a «Consulta detallada»', 'Consulta detallada'],
        ];
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
