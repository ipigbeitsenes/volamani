<?php

namespace App\Enums;

enum PricingCategory: string
{
    case Website = 'website';
    case Branding = 'branding';
    case AiAutomation = 'ai_automation';
    case Software = 'software';
    case DigitalMarketing = 'digital_marketing';
    case SocialMedia = 'social_media';
    case AgencyRetainer = 'agency_retainer';
    case Consulting = 'consulting';

    public function label(): string
    {
        return match ($this) {
            self::Website => 'Website Development',
            self::Branding => 'Branding & Design',
            self::AiAutomation => 'AI & Automation',
            self::Software => 'Software Development',
            self::DigitalMarketing => 'Digital Marketing',
            self::SocialMedia => 'Social Media Management',
            self::AgencyRetainer => 'Agency Retainer',
            self::Consulting => 'Consulting Services',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Website => 'bi-globe',
            self::Branding => 'bi-palette',
            self::AiAutomation => 'bi-robot',
            self::Software => 'bi-code-slash',
            self::DigitalMarketing => 'bi-megaphone',
            self::SocialMedia => 'bi-instagram',
            self::AgencyRetainer => 'bi-building',
            self::Consulting => 'bi-person-workspace',
        };
    }

    public static function options(): array
    {
        return array_map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
            'icon' => $case->icon(),
        ], self::cases());
    }
}
