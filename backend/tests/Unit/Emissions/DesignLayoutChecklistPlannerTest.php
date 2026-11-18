<?php

namespace Tests\Unit\Emissions;

use App\Services\Emissions\DesignAssistant\DesignLayoutActionPlanner;
use PaqSuite\LaravelCore\ChatAssistant\Contracts\LlmChatCompletionClient;
use PaqSuite\LaravelCore\Llm\LlmCredentialContext;
use PHPUnit\Framework\TestCase;

/**
 * Evalúa prompts de la matriz Framework
 * docs/02-producto/15-reportes-emisiones-ia-checklist-matriz.json
 */
final class DesignLayoutChecklistPlannerTest extends TestCase
{
    private const MATRIX_PATH = 'C:/Programacion/PaqSuite-IA-FRAMEWORK/docs/02-producto/15-reportes-emisiones-ia-checklist-matriz.json';

    /**
     * @dataProvider checklistMatrixProvider
     *
     * @param list<array{action: string, payload?: array<string, mixed>}> $expectedActions
     */
    public function test_checklist_planner_expectations(
        string $checkId,
        string $prompt,
        string $plannerExpect,
        array $expectedActions,
    ): void {
        if ($checkId === 'CHK-00') {
            $this->markTestSkipped('Matriz checklist no encontrada en ruta Framework.');
        }

        $planner = new DesignLayoutActionPlanner($this->unusedLlm());
        $result = $planner->fromHeuristics($prompt);
        $actions = $result['actions'];

        if ($plannerExpect === 'no_mapped') {
            $this->assertSame([], $actions, "{$checkId}: se esperaba sin acciones");
            $this->assertSame(
                'partes.emissionDesign.noMappedActions',
                $result['replyText'],
                "{$checkId}: reply de no mapeo"
            );

            return;
        }

        $this->assertNotSame([], $actions, "{$checkId}: se esperaban acciones");

        foreach ($expectedActions as $index => $expected) {
            $this->assertArrayHasKey($index, $actions, "{$checkId}: falta acción índice {$index}");
            $this->assertSame($expected['action'], $actions[$index]['action'] ?? null, "{$checkId}: action");

            if (isset($expected['payload'])) {
                foreach ($expected['payload'] as $key => $value) {
                    $this->assertSame(
                        $value,
                        $actions[$index]['payload'][$key] ?? null,
                        "{$checkId}: payload.{$key}"
                    );
                }
            }
        }
    }

    public static function checklistMatrixProvider(): array
    {
        if (! is_readable(self::MATRIX_PATH)) {
            return [
                'matrix_missing' => [
                    'CHK-00',
                    'skip',
                    'no_mapped',
                    [],
                ],
            ];
        }

        $decoded = json_decode((string) file_get_contents(self::MATRIX_PATH), true, 512, JSON_THROW_ON_ERROR);
        $items = is_array($decoded['items'] ?? null) ? $decoded['items'] : [];
        $rows = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }
            if (! in_array('phpunit_planner', $item['testLayers'] ?? [], true)) {
                continue;
            }

            $plannerExpect = (string) ($item['plannerExpect'] ?? 'no_mapped');
            $expectedActions = [];
            foreach ($item['expectedActions'] ?? [] as $action) {
                if (! is_array($action)) {
                    continue;
                }
                $row = ['action' => (string) ($action['action'] ?? '')];
                if (isset($action['payload']) && is_array($action['payload'])) {
                    $row['payload'] = $action['payload'];
                }
                $expectedActions[] = $row;
            }

            $rows[(string) ($item['id'] ?? 'CHK')] = [
                (string) ($item['id'] ?? 'CHK'),
                (string) ($item['promptCanonico'] ?? ''),
                $plannerExpect,
                $expectedActions,
            ];
        }

        return $rows === [] ? ['empty' => ['CHK-00', 'x', 'no_mapped', []]] : $rows;
    }

    public function test_matrix_file_is_readable(): void
    {
        $this->assertFileIsReadable(self::MATRIX_PATH);
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