<?php

namespace App\Enums;

enum ProductType: string
{
    case Digital = 'digital';
    case Template = 'template';
    case EBook = 'ebook';
    case Software = 'software';
    case UIKit = 'ui_kit';
    case Course = 'course';
    case Asset = 'asset';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Digital => 'Digital Product',
            self::Template => 'Template',
            self::EBook => 'eBook',
            self::Software => 'Software',
            self::UIKit => 'UI Kit',
            self::Course => 'Course',
            self::Asset => 'Digital Asset',
            self::Other => 'Other',
        };
    }
}
