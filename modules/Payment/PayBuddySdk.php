<?php

namespace Modules\Payment;

use Illuminate\Support\Str;
use NumberFormatter;

final class PayBuddySdk
{
    public function charge(string $token, int $amountInCents, string $statementDescription): array
    {
        $this->validateToken($token);

        $numberFormatter = new NumberFormatter('en-US', NumberFormatter::CURRENCY);

        return [
            'id' => (string) Str::uuid(),
            'amount_in_cents' => $amountInCents,
            'localized_amount' => $numberFormatter->format($amountInCents / 100),
            'statement_description' => $statementDescription,
            'created_at' => now()->toDateTimeString(),
        ];
    }

    public static function make(): PayBuddySdk
    {
        return new self();
    }

    public static function validToken(): string
    {
        return (string) Str::uuid();
    }

    public static function invalidToken(): string
    {
        return substr(self::validToken(), -35);
    }

    /**
     * @throws \RuntimeException
     */
    protected function validateToken(string $token): void
    {
        if (! Str::isUuid($token)) {
            throw new \RuntimeException('The given payment token is not valid.');
        }
    }
}
