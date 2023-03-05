<?php

declare(strict_types=1);

namespace Service\Billing;

use Service\Billing\Exception\BillingException;

class Creator
{
    public function pay(string $billingType, float $totalPrice): void
    {
        match ($billingType) {
            'bank_transfer' => $this->payByBankTransfer($totalPrice),
            'card' => $this->payByCard($totalPrice),
            default => throw new BillingException('unknown payment type'),
        };
    }

    protected function payByBankTransfer(float $totalPrice): void
    {
        (new BankTransfer())->pay($totalPrice);
    }

    protected function payByCard(float $totalPrice): void
    {
        (new Card())->pay($totalPrice);
    }
}
