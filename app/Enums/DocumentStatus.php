<?php

namespace App\Enums;

enum DocumentStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Viewed = 'viewed';
    case Partial = 'partial';     // invoice partially paid
    case Paid = 'paid';        // invoice settled
    case Overdue = 'overdue';     // invoice past due, unpaid
    case Accepted = 'accepted';    // quotation accepted by client
    case Signed = 'signed';      // contract of sale e-signed by client
    case Declined = 'declined';    // quotation declined
    case Expired = 'expired';     // quotation past valid_until
    case Converted = 'converted';   // quotation turned into an invoice
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function badge(): string
    {
        return match ($this) {
            self::Draft => 'secondary',
            self::Sent => 'info',
            self::Viewed => 'info',
            self::Partial => 'warning',
            self::Paid => 'success',
            self::Overdue => 'danger',
            self::Accepted => 'success',
            self::Signed => 'success',
            self::Declined => 'danger',
            self::Expired => 'secondary',
            self::Converted => 'primary',
            self::Cancelled => 'dark',
        };
    }

    /** Statuses where the document is still a live, editable draft. */
    public function isEditable(): bool
    {
        return $this === self::Draft;
    }
}
