<?php

namespace Tests\Unit;

use App\Services\Auth\PasswordResetLinkBuilder;
use Tests\TestCase;

final class PasswordResetLinkBuilderTest extends TestCase
{
    public function test_host_plesk_canonico_apunta_al_spa_vercel_con_cliente(): void
    {
        config()->set('app.frontend_url', 'https://paq.partesatencion.paqsystems.com');
        config()->set('app.frontend_spa_url', '');

        $url = (new PasswordResetLinkBuilder)->build('abcToken', 'es', 'paq');

        $this->assertStringStartsWith(
            'https://partesatencionpaqsystems.vercel.app/reset-password?',
            $url
        );
        $this->assertStringContainsString('token=abcToken', $url);
        $this->assertStringContainsString('locale=es', $url);
        $this->assertStringContainsString('cliente=PAQ', $url);
    }

    public function test_localhost_no_se_reescribe(): void
    {
        config()->set('app.frontend_url', 'http://localhost:5173');
        config()->set('app.frontend_spa_url', '');

        $url = (new PasswordResetLinkBuilder)->build('tok', 'en');

        $this->assertSame('http://localhost:5173/reset-password?token=tok&locale=en', $url);
    }

    public function test_frontend_spa_url_explícito_gana(): void
    {
        config()->set('app.frontend_url', 'https://paq.partesatencion.paqsystems.com');
        config()->set('app.frontend_spa_url', 'https://spa.example.test');

        $url = (new PasswordResetLinkBuilder)->build('tok', 'es', 'DEMO');

        $this->assertStringStartsWith('https://spa.example.test/reset-password?', $url);
        $this->assertStringContainsString('cliente=DEMO', $url);
    }

    public function test_desarrollo_usa_vercel_dev(): void
    {
        config()->set('app.frontend_url', 'https://desarrollo.partesatencion.paqsystems.com');
        config()->set('app.frontend_spa_url', '');

        $base = (new PasswordResetLinkBuilder)->resolveSpaBaseUrl();

        $this->assertSame('https://partesatencionpaqsystemsdev.vercel.app', $base);
    }
}
