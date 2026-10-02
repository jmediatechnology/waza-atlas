<?php

namespace App\Entity;

enum Family: string
{
    case Nage = 'nage';
    case Katame = 'katame';

    public function label(): string
    {
        return match ($this) {
            self::Nage => 'Nage-waza',
            self::Katame => 'Katame-waza',
        };
    }

    public function kanji(): string
    {
        return match ($this) {
            self::Nage => '投技',
            self::Katame => '固技',
        };
    }

    public function english(): string
    {
        return match ($this) {
            self::Nage => 'Throwing techniques',
            self::Katame => 'Grappling techniques',
        };
    }
}
