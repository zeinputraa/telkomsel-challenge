<?php

namespace App\Enums;

enum StatusInsiden: string
{
    case MenungguVerifikasiStaff = 'menunggu_verifikasi_staff';
    case TerverifikasiStaff = 'terverifikasi_staff';
    case MenungguFinalisasiAdmin = 'menunggu_finalisasi_admin';
    case DibatalkanDitemukan = 'dibatalkan_ditemukan';
    case DifinalisasiAdmin = 'difinalisasi_admin';
}
