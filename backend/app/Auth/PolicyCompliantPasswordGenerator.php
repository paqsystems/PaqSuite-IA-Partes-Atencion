<?php

namespace App\Auth;

use PaqSuite\LaravelCore\Auth\PasswordPolicy;
use RuntimeException;

/**
 * Genera una clave que pasa PasswordPolicy (simple/segura + longitud GEN).
 */
final class PolicyCompliantPasswordGenerator
{
    private const SPECIALS = '!@#$%&*_-+=.?';

    public function __construct(
        private readonly PasswordPolicy $passwordPolicy
    ) {
    }

    public function generate(): string
    {
        for ($attempt = 0; $attempt < 24; $attempt++) {
            $candidate = $this->candidate();
            if ($this->passwordPolicy->isOk($candidate)) {
                return $candidate;
            }
        }

        throw new RuntimeException('Could not generate a policy-compliant password');
    }

    private function candidate(): string
    {
        $min = max(8, $this->passwordPolicy->longitudMin());
        if ($this->passwordPolicy->complexityMode() === 'segura') {
            $chars = [
                $this->pick('ABCDEFGHJKLMNPQRSTUVWXYZ'),
                $this->pick('abcdefghijkmnopqrstuvwxyz'),
                $this->pick('23456789'),
                $this->pick(self::SPECIALS),
            ];
            $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789'.self::SPECIALS;
            for ($i = count($chars); $i < $min; $i++) {
                $chars[] = $this->pick($alphabet);
            }
            shuffle($chars);

            return implode('', $chars);
        }

        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
        $out = '';
        for ($i = 0; $i < $min; $i++) {
            $out .= $this->pick($alphabet);
        }

        return $out;
    }

    private function pick(string $alphabet): string
    {
        return $alphabet[random_int(0, strlen($alphabet) - 1)];
    }
}
