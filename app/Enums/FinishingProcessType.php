<?php

namespace App\Enums;

enum FinishingProcessType: string
{
    case CELUP = 'celup';
    case MOLEN = 'molen';
    case KEROK = 'kerok';
    case BOR = 'bor';

    public function label(): string
    {
        return match ($this) {
            self::CELUP => 'Celup Metanol',
            self::MOLEN => 'Molen (Digiling Halus)',
            self::KEROK => 'Kerok (Dibentuk)',
            self::BOR => 'Bor (Dibentuk)',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::CELUP => 'Kayu dicelup menggunakan metanol.',
            self::MOLEN => 'Kayu digiling dengan molen untuk dihaluskan.',
            self::KEROK => 'Kayu dikerok untuk dibentuk.',
            self::BOR => 'Kayu dibor untuk dibentuk.',
        };
    }

    /**
     * Proses yang secara wajar menghasilkan ampas buangan.
     */
    public function producesWaste(): bool
    {
        return $this !== self::CELUP;
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
