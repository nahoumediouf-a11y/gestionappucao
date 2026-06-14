<?php

namespace App\Support;

class Captcha
{
    /**
     * Génère une question mathématique simple et stocke la réponse en session.
     *
     * @return array{question: string, a: int, b: int}
     */
    public static function generate(): array
    {
        $a = random_int(1, 9);
        $b = random_int(1, 9);

        session(['captcha_answer' => $a + $b]);

        return [
            'question' => "{$a} + {$b}",
            'a' => $a,
            'b' => $b,
        ];
    }

    public static function verify(?string $input): bool
    {
        $expected = session('captcha_answer');
        session()->forget('captcha_answer');

        return $expected !== null && $input !== null && (int) $input === (int) $expected;
    }
}
