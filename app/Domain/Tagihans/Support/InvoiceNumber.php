<?php

namespace App\Domain\Tagihans\Support;

use Illuminate\Support\Str;

class InvoiceNumber
{
    /**
     * Format: INV-{YYYY}{MM}-{RAND5}
     */
    public static function make(int $tahun, string $idBulan): string
    {
        $mm = str_pad($idBulan, 2, '0', STR_PAD_LEFT);
        return 'INV-' . $tahun . $mm . '-' . strtoupper(Str::random(5));
    }
}
