<?php

namespace App\Enums;

enum StatusGantiRugi: string
{
    case BelumDiselesaikan = 'belum_diselesaikan';
    case ProsesGantiRugi = 'proses_ganti_rugi';
    case Selesai = 'selesai';
}
