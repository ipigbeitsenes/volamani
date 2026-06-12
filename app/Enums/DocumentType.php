<?php

namespace App\Enums;

enum DocumentType: string
{
    case Invoice   = 'invoice';
    case Quotation = 'quotation';

    public function label(): string
    {
        return match ($this) {
            self::Invoice   => 'Invoice',
            self::Quotation => 'Quotation',
        };
    }

    /** Reference prefix used in document numbers. */
    public function prefix(): string
    {
        return match ($this) {
            self::Invoice   => 'INV',
            self::Quotation => 'QUO',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Invoice   => 'bi-receipt',
            self::Quotation => 'bi-file-earmark-text',
        };
    }

    public function isInvoice(): bool
    {
        return $this === self::Invoice;
    }
}
