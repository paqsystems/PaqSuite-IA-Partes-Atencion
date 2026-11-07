<?php

namespace Tests\Unit;

use App\Auth\PolicyCompliantPasswordGenerator;
use PaqSuite\LaravelCore\Auth\ParametroStore;
use PaqSuite\LaravelCore\Auth\PasswordPolicy;
use PHPUnit\Framework\TestCase;

final class PolicyCompliantPasswordGeneratorTest extends TestCase
{
    public function test_genera_clave_simple_de_longitud_minima(): void
    {
        $policy = $this->policy('simple', 10);
        $plain = (new PolicyCompliantPasswordGenerator($policy))->generate();

        $this->assertGreaterThanOrEqual(10, strlen($plain));
        $this->assertTrue($policy->isOk($plain));
    }

    public function test_genera_clave_segura_con_clases_requeridas(): void
    {
        $policy = $this->policy('segura', 8);
        $plain = (new PolicyCompliantPasswordGenerator($policy))->generate();

        $this->assertMatchesRegularExpression('/[A-Z]/', $plain);
        $this->assertMatchesRegularExpression('/[a-z]/', $plain);
        $this->assertMatchesRegularExpression('/\d/', $plain);
        $this->assertMatchesRegularExpression('/[!@#$%&*_\-+=.?]/', $plain);
        $this->assertTrue($policy->isOk($plain));
    }

    private function policy(string $mode, int $min): PasswordPolicy
    {
        $store = $this->createMock(ParametroStore::class);
        $store->method('getString')->willReturn($mode);
        $store->method('getInt')->willReturn($min);

        return new PasswordPolicy($store);
    }
}
