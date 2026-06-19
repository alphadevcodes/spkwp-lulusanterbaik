<?php

namespace App\Enums;

enum CriteriaCategory: string
{
    case UTAMA = 'utama';
    case TAMBAHAN = 'tambahan';

    public function label(): string
    {
        return match ($this) {
            self::UTAMA => 'Utama',
            self::TAMBAHAN => 'Tambahan',
        };
    }

    /**
     * Untuk dipakai di flux:select / flux:radio sebagai options.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }
}
