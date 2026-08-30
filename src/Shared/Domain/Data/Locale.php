<?php

declare(strict_types=1);

namespace App\Shared\Domain\Data;

use Symfony\Component\Translation\TranslatableMessage;
use function Symfony\Component\Translation\t;

enum Locale: string
{
    case EN = 'en';

    case FR = 'fr';

    public static function default(): self
    {
        return self::EN;
    }

    public function flag(): string
    {
        return match ($this) {
            self::EN => '🇬🇧',
            self::FR => '🇫🇷',
        };
    }

    public function toTranslatable(): TranslatableMessage
    {
        return t('locale.' . $this->value);
    }
}
