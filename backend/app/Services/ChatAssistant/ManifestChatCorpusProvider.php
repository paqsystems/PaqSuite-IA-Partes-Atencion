<?php

namespace App\Services\ChatAssistant;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use PaqSuite\LaravelCore\ChatAssistant\Contracts\ChatCorpusProvider;
use PaqSuite\LaravelCore\ChatAssistant\CorpusChunk;

/**
 * Corpus documental host (TR-008): manifest Partes + GEN, sin RAG.
 */
final class ManifestChatCorpusProvider implements ChatCorpusProvider
{
    public function resolveContext(string $message): array
    {
        $entries = $this->buildEntries();
        $maxChars = max(1024, (int) config('chat_assistant_corpus.maxChars', 28 * 1024));

        if ($entries === []) {
            Log::warning('chat_assistant.corpus.empty_manifest');

            return [];
        }

        $hasGen = false;
        foreach ($entries as $entry) {
            if (($entry['origin'] ?? '') === 'gen') {
                $hasGen = true;
                break;
            }
        }
        if (! $hasGen) {
            Log::warning('chat_assistant.corpus.gen_root_missing');
        }

        $tokens = $this->tokenize($message);
        $scored = [];
        foreach ($entries as $index => $entry) {
            $path = (string) ($entry['path'] ?? '');
            if ($path === '' || ! is_readable($path)) {
                continue;
            }
            $content = (string) file_get_contents($path);
            if (trim($content) === '') {
                continue;
            }
            $haystack = mb_strtolower(($entry['title'] ?? '').' '.$content);
            $score = 0;
            foreach ($tokens as $token) {
                if (str_contains($haystack, $token)) {
                    $score++;
                }
            }
            if ($score === 0 && $tokens !== []) {
                continue;
            }
            $scored[] = [
                'score' => $score === 0 ? 1 : $score,
                'index' => $index,
                'title' => (string) ($entry['title'] ?? basename($path)),
                'content' => $content,
                'locator' => isset($entry['locator']) ? (string) $entry['locator'] : null,
            ];
        }

        if ($scored === [] && $tokens !== []) {
            foreach ($entries as $entry) {
                if (($entry['origin'] ?? '') !== 'partes') {
                    continue;
                }
                $path = (string) ($entry['path'] ?? '');
                if ($path === '' || ! is_readable($path)) {
                    continue;
                }
                $name = strtolower(basename($path));
                if ($name === 'readme.md' || $name === 'partes-atencion.md') {
                    $content = (string) file_get_contents($path);
                    if (trim($content) === '') {
                        continue;
                    }
                    $scored[] = [
                        'score' => 1,
                        'index' => 0,
                        'title' => (string) ($entry['title'] ?? basename($path)),
                        'content' => $content,
                        'locator' => isset($entry['locator']) ? (string) $entry['locator'] : null,
                    ];
                    break;
                }
            }
        }

        usort($scored, static function (array $a, array $b): int {
            return $b['score'] <=> $a['score'] ?: $a['index'] <=> $b['index'];
        });

        $chunks = [];
        $used = 0;
        foreach ($scored as $item) {
            $slice = $this->truncate($item['content'], max(512, $maxChars - $used));
            if ($slice === '') {
                break;
            }
            $chunks[] = new CorpusChunk($item['title'], $slice, $item['locator']);
            $used += mb_strlen($slice);
            if ($used >= $maxChars || count($chunks) >= 6) {
                break;
            }
        }

        return $chunks;
    }

    /**
     * @return list<array{title: string, path: string, locator: string, origin: string}>
     */
    private function buildEntries(): array
    {
        /** @var list<array{title: string, path: string, locator?: string, origin?: string}>|null $configured */
        $configured = config('chat_assistant_corpus.entries');
        if (is_array($configured) && $configured !== []) {
            return array_values($configured);
        }

        $entries = [];
        $partesRoot = (string) config('chat_assistant_corpus.partesManualRoot', '');
        $entries = array_merge($entries, $this->scanMarkdownDir($partesRoot, 'partes', 'Partes'));

        $genRoot = config('chat_assistant_corpus.genDocsRoot');
        if (! is_string($genRoot) || $genRoot === '') {
            $fallback = (string) config('chat_assistant_corpus.genDocsRootFallback', '');
            $genRoot = is_dir($fallback) ? $fallback : null;
        }
        if (is_string($genRoot) && $genRoot !== '') {
            $entries = array_merge($entries, $this->scanMarkdownDir($genRoot, 'gen', 'Framework'));
        }

        return $entries;
    }

    /**
     * @return list<array{title: string, path: string, locator: string, origin: string}>
     */
    private function scanMarkdownDir(string $root, string $origin, string $titlePrefix): array
    {
        if ($root === '' || ! is_dir($root)) {
            return [];
        }

        $entries = [];
        foreach (File::files($root) as $file) {
            if (strtolower($file->getExtension()) !== 'md') {
                continue;
            }
            if (str_starts_with($file->getFilename(), '_')) {
                continue;
            }
            $entries[] = [
                'title' => $titlePrefix.' — '.$file->getFilenameWithoutExtension(),
                'path' => $file->getPathname(),
                'locator' => $origin.'/'.$file->getFilename(),
                'origin' => $origin,
            ];
        }

        return $entries;
    }

    /**
     * @return list<string>
     */
    private function tokenize(string $message): array
    {
        $normalized = mb_strtolower(trim($message));
        if ($normalized === '') {
            return [];
        }
        $parts = preg_split('/[^\p{L}\p{N}]+/u', $normalized) ?: [];
        $tokens = [];
        foreach ($parts as $part) {
            if (mb_strlen($part) < 3) {
                continue;
            }
            $tokens[$part] = $part;
        }

        return array_values($tokens);
    }

    private function truncate(string $content, int $maxChars): string
    {
        $trimmed = trim($content);
        if (mb_strlen($trimmed) <= $maxChars) {
            return $trimmed;
        }

        return rtrim(mb_substr($trimmed, 0, $maxChars - 1)).'…';
    }
}
