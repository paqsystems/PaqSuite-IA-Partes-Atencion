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

    public function test_heuristics_chk03_set_report_title(): void
    {
        $planner = new DesignLayoutActionPlanner($this->unusedLlm());
        $result = $planner->fromHeuristics('Cambiá el título a Consulta detallada');

        $this->assertCount(1, $result['actions']);
        $this->assertSame('setReportTitle', $result['actions'][0]['action']);
        $this->assertSame('Consulta detallada', $result['actions'][0]['payload']['title'] ?? null);
    }

    public function test_heuristics_add_detail_static_text_borrador(): void
    {
        $planner = new DesignLayoutActionPlanner($this->unusedLlm());
        $result = $planner->fromHeuristics('Agregá un texto fijo que diga Borrador en el detalle');

        $this->assertCount(1, $result['actions']);
        $this->assertSame('addDetailStaticText', $result['actions'][0]['action']);
        $this->assertSame('Borrador', $result['actions'][0]['payload']['text'] ?? null);
    }

    public function test_heuristics_add_detail_field_observacion(): void
    {
        $planner = new DesignLayoutActionPlanner($this->unusedLlm());
        $result = $planner->fromHeuristics('Agregá la columna observación en el detalle');

        $this->assertCount(1, $result['actions']);
        $this->assertSame('addDetailField', $result['actions'][0]['action']);
        $this->assertSame('observacion', $result['actions'][0]['payload']['field'] ?? null);
    }

    public function test_heuristics_cliente_code_appearance(): void
    {
        $planner = new DesignLayoutActionPlanner($this->unusedLlm());
        $prompt = 'Al código de cliente asignale Times New Roman 12, azul marino, negrita, subrayado e italica, centrado';
        $result = $planner->fromHeuristics($prompt);

        $this->assertCount(1, $result['actions']);
        $this->assertSame('setControlAppearance', $result['actions'][0]['action']);
        $payload = $result['actions'][0]['payload'];
        $this->assertSame('field', $payload['target']['kind'] ?? null);
        $this->assertSame('clienteCode', $payload['target']['fieldName'] ?? null);
        $this->assertSame('Times New Roman', $payload['fontFamily'] ?? null);
        $this->assertSame(12, $payload['fontSizePt'] ?? null);
        $this->assertSame('#001f3f', $payload['foreColor'] ?? null);
    }

    public function test_heuristics_title_appearance(): void
    {
        $planner = new DesignLayoutActionPlanner($this->unusedLlm());
        $result = $planner->fromHeuristics('Poné el título en negrita, subrayado y centrado');

        $this->assertCount(1, $result['actions']);
        $this->assertSame('setControlAppearance', $result['actions'][0]['action']);
        $this->assertSame('reportTitle', $result['actions'][0]['payload']['target']['kind'] ?? null);
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
