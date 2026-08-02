<?php

namespace Tests\Unit\ChatAssistant;

use App\Services\ChatAssistant\ManifestChatCorpusProvider;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

final class ManifestChatCorpusProviderTest extends TestCase
{
    public function test_resuelve_chunks_por_tokens_del_mensaje(): void
    {
        $dir = storage_path('framework/testing-corpus-'.uniqid());
        File::ensureDirectoryExists($dir);
        File::put($dir.'/carga.md', "# Carga diaria\nComo cargar un parte de atencion.");
        File::put($dir.'/otro.md', "# Otro tema\nContenido irrelevante.");

        config([
            'chat_assistant_corpus.maxChars' => 8000,
            'chat_assistant_corpus.entries' => [
                [
                    'title' => 'Partes — carga',
                    'path' => $dir.'/carga.md',
                    'locator' => 'partes/carga.md',
                    'origin' => 'partes',
                ],
                [
                    'title' => 'Partes — otro',
                    'path' => $dir.'/otro.md',
                    'locator' => 'partes/otro.md',
                    'origin' => 'partes',
                ],
            ],
        ]);

        $provider = new ManifestChatCorpusProvider();
        $chunks = $provider->resolveContext('necesito ayuda para cargar partes');

        $this->assertNotEmpty($chunks);
        $this->assertSame('Partes — carga', $chunks[0]->title);

        File::deleteDirectory($dir);
    }
}
