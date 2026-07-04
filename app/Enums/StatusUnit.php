<?php

namespace App\Enums;

enum StatusUnit: string
{
    case Tersedia = 'tersedia';
    case Dipinjam = 'dipinjam';
    case Maintenance = 'maintenance';
    case DilaporkanHilang = 'dilaporkan_hilang';
    case HilangPermanen = 'hilang_permanen';
}
