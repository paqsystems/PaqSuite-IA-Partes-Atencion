<?php

namespace App\Services\Emissions;

use PaqSuite\LaravelCore\Emissions\Dto\EmissionArtifact;

final class MinimalPdfArtifactFactory
{
    public function make(string $seed, string $fileName): EmissionArtifact
    {
        return new EmissionArtifact($this->pdfBytes($seed), $fileName, 'application/pdf');
    }

    private function pdfBytes(string $seed): string
    {
        $text = 'PaqSuite emission '.$seed;

        return "%PDF-1.4\n"
            ."1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n"
            ."2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n"
            ."3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 595 842]>>endobj\n"
            ."% {$text}\n"
            ."trailer<</Root 1 0 R>>\n"
            .'%%EOF';
    }
}
