<?php

namespace App\Enums;

enum DocumentType: string
{
    case Invoice   = 'invoice';
    case Quotation = 'quotation';
    case Contract  = 'contract';

    public function label(): string
    {
        return match ($this) {
            self::Invoice   => 'Invoice',
            self::Quotation => 'Quotation',
            self::Contract  => 'Contract of Sale',
        };
    }

    /** Reference prefix used in document numbers. */
    public function prefix(): string
    {
        return match ($this) {
            self::Invoice   => 'INV',
            self::Quotation => 'QUO',
            self::Contract  => 'CON',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Invoice   => 'bi-receipt',
            self::Quotation => 'bi-file-earmark-text',
            self::Contract  => 'bi-file-earmark-check',
        };
    }

    public function isInvoice(): bool
    {
        return $this === self::Invoice;
    }

    public function isContract(): bool
    {
        return $this === self::Contract;
    }
}
