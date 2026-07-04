<?php

namespace App\Enums;

enum JenisInsiden: string
{
    case RusakRingan = 'rusak_ringan';
    case RusakBerat = 'rusak_berat';
    case Hilang = 'hilang';
}
