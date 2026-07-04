<?php

namespace App\Enums;

enum StatusBorrowing: string
{
    case Diajukan = 'diajukan';
    case Disetujui = 'disetujui';
    case Ditolak = 'ditolak';
    case Berjalan = 'berjalan';
    case Selesai = 'selesai';
    case DibatalkanUser = 'dibatalkan_user';
    case DibatalkanOtomatis = 'dibatalkan_otomatis';
}
